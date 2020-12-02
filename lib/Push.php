<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Notifications;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\Security\IdentityProof\Key;
use OC\Security\IdentityProof\Manager;
use OCP\AppFramework\Http;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\IManager as INotificationManager;
use OCP\Notification\INotification;
use OCP\UserStatus\IManager as IUserStatusManager;
use OCP\UserStatus\IUserStatus;
use Symfony\Component\Console\Output\OutputInterface;

class Push {
	/** @var IDBConnection */
	protected $db;
	/** @var INotificationManager */
	protected $notificationManager;
	/** @var IConfig */
	protected $config;
	/** @var IProvider */
	protected $tokenProvider;
	/** @var Manager */
	private $keyManager;
	/** @var IUserManager */
	private $userManager;
	/** @var IClientService */
	protected $clientService;
	/** @var ICache */
	protected $cache;
	/** @var IUserStatusManager */
	protected $userStatusManager;
	/** @var IFactory */
	protected $l10nFactory;
	/** @var ILogger */
	protected $log;
	/** @var OutputInterface */
	protected $output;
	/** @var array */
	protected $payloadsToSend = [];
	/** @var bool */
	protected $deferPayloads = false;

	public function __construct(IDBConnection $connection,
								INotificationManager $notificationManager,
								IConfig $config,
								IProvider $tokenProvider,
								Manager $keyManager,
								IUserManager $userManager,
								IClientService $clientService,
								ICacheFactory $cacheFactory,
								IUserStatusManager $userStatusManager,
								IFactory $l10nFactory,
								ILogger $log) {
		$this->db = $connection;
		$this->notificationManager = $notificationManager;
		$this->config = $config;
		$this->tokenProvider = $tokenProvider;
		$this->keyManager = $keyManager;
		$this->userManager = $userManager;
		$this->clientService = $clientService;
		$this->cache = $cacheFactory->createDistributed('pushtokens');
		$this->userStatusManager = $userStatusManager;
		$this->l10nFactory = $l10nFactory;
		$this->log = $log;
	}

	public function setOutput(OutputInterface $output): void {
		$this->output = $output;
	}

	protected function printInfo(string $message): void {
		if ($this->output) {
			$this->output->writeln($message);
		}
	}

	public function isDeferring(): bool {
		return $this->deferPayloads;
	}

	public function deferPayloads(): void {
		$this->deferPayloads = true;
	}

	public function flushPayloads(): void {
		$this->deferPayloads = false;
		$this->sendNotificationsToProxies();
	}

	public function pushToDevice(int $id, INotification $notification, ?OutputInterface $output = null): void {
		if (!$this->config->getSystemValueBool('has_internet_connection', true)) {
			return;
		}

		$user = $this->userManager->get($notification->getUser());
		if (!($user instanceof IUser)) {
			return;
		}

		$userStatus = $this->userStatusManager->getUserStatuses([
			$notification->getUser(),
		]);

		if (isset($userStatus[$notification->getUser()])) {
			$userStatus = $userStatus[$notification->getUser()];
			if ($userStatus->getStatus() === IUserStatus::DND) {
				$this->printInfo('User status is set to DND');
				return;
			}
		}

		$devices = $this->getDevicesForUser($notification->getUser());
		if (empty($devices)) {
			$this->printInfo('No devices found for user');
			return;
		}

		$this->printInfo('Trying to push to ' . count($devices) . ' devices');
		$this->printInfo('');

		$language = $this->l10nFactory->getUserLanguage($user);
		$this->printInfo('Language is set to ' . $language);

		try {
			$this->notificationManager->setPreparingPushNotification(true);
			$notification = $this->notificationManager->prepare($notification, $language);
		} catch (\InvalidArgumentException $e) {
			return;
		} finally {
			$this->notificationManager->setPreparingPushNotification(false);
		}

		$userKey = $this->keyManager->getKey($user);

		$this->printInfo('Private user key size: ' . strlen($userKey->getPrivate()));
		$this->printInfo('Public user key size: ' . strlen($userKey->getPublic()));

		$isTalkNotification = \in_array($notification->getApp(), ['spreed', 'talk', 'admin_notification_talk'], true);
		$talkDevices = array_filter($devices, function ($device) {
			return $device['apptype'] === 'talk';
		});
		$otherDevices = array_filter($devices, function ($device) {
			return $device['apptype'] !== 'talk';
		});

		$this->printInfo('Identified ' . count($talkDevices) . ' Talk devices and ' . count($otherDevices) . ' others.');

		if (!$isTalkNotification) {
			if (empty($otherDevices)) {
				// We only send file notifications to the files app.
				// If you don't have such a device, bye!
				return;
			}
			$devices = $otherDevices;
		} else {
			if (empty($talkDevices)) {
				// If you don't have a talk device,
				// we fall back to the files app.
				$devices = $otherDevices;
			} else {
				$devices = $talkDevices;
			}
		}

		// We don't push to devices that are older than 60 days
		$maxAge = time() - 60 * 24 * 60 * 60;

		foreach ($devices as $device) {
			$this->printInfo('');
			$this->printInfo('Device token:' . $device['token']);

			if (!$this->validateToken($device['token'], $maxAge)) {
				// Token does not exist anymore
				continue;
			}

			try {
				$payload = json_encode($this->encryptAndSign($userKey, $device, $id, $notification, $isTalkNotification));

				$proxyServer = rtrim($device['proxyserver'], '/');
				if (!isset($this->payloadsToSend[$proxyServer])) {
					$this->payloadsToSend[$proxyServer] = [];
				}
				$this->payloadsToSend[$proxyServer][] = $payload;
			} catch (\InvalidArgumentException $e) {
				// Failed to encrypt message for device: public key is invalid
				$this->deletePushToken($device['token']);
			}
		}

		if (!$this->deferPayloads) {
			$this->sendNotificationsToProxies();
		}
	}

	public function pushDeleteToDevice(string $userId, int $notificationId): void {
		if (!$this->config->getSystemValueBool('has_internet_connection', true)) {
			return;
		}

		$user = $this->userManager->get($userId);
		if (!($user instanceof IUser)) {
			return;
		}

		$devices = $this->getDevicesForUser($userId);
		if (empty($devices)) {
			return;
		}

		// We don't push to devices that are older than 60 days
		$maxAge = time() - 60 * 24 * 60 * 60;

		$userKey = $this->keyManager->getKey($user);
		foreach ($devices as $device) {
			if (!$this->validateToken($device['token'], $maxAge)) {
				// Token does not exist anymore
				continue;
			}

			try {
				$payload = json_encode($this->encryptAndSignDelete($userKey, $device, $notificationId));

				$proxyServer = rtrim($device['proxyserver'], '/');
				if (!isset($this->payloadsToSend[$proxyServer])) {
					$this->payloadsToSend[$proxyServer] = [];
				}
				$this->payloadsToSend[$proxyServer][] = $payload;
			} catch (\InvalidArgumentException $e) {
				// Failed to encrypt message for device: public key is invalid
				$this->deletePushToken($device['token']);
			}
		}

		if (!$this->deferPayloads) {
			$this->sendNotificationsToProxies();
		}
	}

	protected function sendNotificationsToProxies(): void {
		$pushNotifications = $this->payloadsToSend;
		$this->payloadsToSend = [];
		if (empty($pushNotifications)) {
			return;
		}

		$client = $this->clientService->newClient();
		foreach ($pushNotifications as $proxyServer => $notifications) {
			try {
				$response = $client->post($proxyServer . '/notifications', [
					'body' => [
						'notifications' => $notifications,
					],
				]);
			} catch (ClientException $e) {
				// Server responded with 4xx (400 Bad Request mostlikely)
				$response = $e->getResponse();
			} catch (ServerException $e) {
				// Server responded with 5xx
				$response = $e->getResponse();
				$body = $response->getBody();
				$error = \is_string($body) ? $body : ('no reason given (' . $response->getStatusCode() . ')');

				$this->log->debug('Could not send notification to push server [{url}]: {error}', [
					'error' => $error,
					'url' => $proxyServer,
					'app' => 'notifications',
				]);

				$this->printInfo('Could not send notification to push server [' . $proxyServer . ']: ' . $error);
				continue;
			} catch (\Exception $e) {
				$this->log->logException($e, [
					'app' => 'notifications',
					'level' => ILogger::ERROR,
				]);

				$error = $e->getMessage() ?: 'no reason given';
				$this->printInfo('Could not send notification to push server [' . get_class($e) . ']: ' . $error);
				continue;
			}

			$status = $response->getStatusCode();
			$body = $response->getBody();
			$bodyData = json_decode($body, true);

			if (is_array($bodyData) && array_key_exists('unknown', $bodyData) && array_key_exists('failed', $bodyData)) {
				if (is_array($bodyData['unknown'])) {
					// Proxy returns null when the array is empty
					foreach ($bodyData['unknown'] as $unknownDevice) {
						$this->printInfo('Deleting device because it is unknown by the push server: ' . $unknownDevice);
						$this->deletePushTokenByDeviceIdentifier($unknownDevice);
					}
				}

				if ($bodyData['failed'] !== 0) {
					$this->printInfo('Push notification sent, but ' . $bodyData['failed'] . ' failed');
				} else {
					$this->printInfo('Push notification sent successfully');
				}
			} elseif ($status !== Http::STATUS_OK) {
				$error = \is_string($body) && $bodyData === null ? $body : 'no reason given';
				$this->printInfo('Could not send notification to push server [' . $proxyServer . ']: ' . $error);
				$this->log->warning('Could not send notification to push server [{url}]: {error}', [
					'error' => $error,
					'url' => $proxyServer,
					'app' => 'notifications',
				]);
			} else {
				$error = \is_string($body) && $bodyData === null ? $body : 'no reason given';
				$this->printInfo('Push notification sent but response was not parsable, using an outdated push proxy? [' . $proxyServer . ']: ' . $error);
				$this->log->info('Push notification sent but response was not parsable, using an outdated push proxy? [{url}]: {error}', [
					'error' => $error,
					'url' => $proxyServer,
					'app' => 'notifications',
				]);
			}
		}
	}

	protected function validateToken(int $tokenId, int $maxAge): bool {
		$age = $this->cache->get('t' . $tokenId);
		if ($age !== null) {
			return $age > $maxAge;
		}

		try {
			// Check if the token is still valid...
			$token = $this->tokenProvider->getTokenById($tokenId);
			$this->cache->set('t' . $tokenId, $token->getLastCheck(), 600);
			return $token->getLastCheck() > $maxAge;
		} catch (InvalidTokenException $e) {
			// Token does not exist anymore, should drop the push device entry
			$this->printInfo('InvalidTokenException is thrown');
			$this->deletePushToken($tokenId);
			$this->cache->set('t' . $tokenId, 0, 600);
			return false;
		}
	}

	/**
	 * @param Key $userKey
	 * @param array $device
	 * @param int $id
	 * @param INotification $notification
	 * @param bool $isTalkNotification
	 * @return array
	 * @throws InvalidTokenException
	 * @throws \InvalidArgumentException
	 */
	protected function encryptAndSign(Key $userKey, array $device, int $id, INotification $notification, bool $isTalkNotification): array {
		$data = [
			'nid' => $id,
			'app' => $notification->getApp(),
			'subject' => '',
			'type' => $notification->getObjectType(),
			'id' => $notification->getObjectId(),
		];

		// Max length of encryption is ~240, so we need to make sure the subject is shorter.
		$maxDataLength = 200 - strlen(json_encode($data));
		$data['subject'] = $this->shortenJsonEncodedMultibyte($notification->getParsedSubject(), $maxDataLength);
		if ($notification->getParsedSubject() !== $data['subject']) {
			$data['subject'] .= '…';
		}

		if ($isTalkNotification) {
			$priority = 'high';
			$type = $data['type'] === 'call' ? 'voip' : 'alert';
		} else {
			$priority = 'normal';
			$type = 'alert';
		}

		$this->printInfo('Device public key size: ' . strlen($device['devicepublickey']));
		$this->printInfo('Data to encrypt is: ' . json_encode($data));

		if (!openssl_public_encrypt(json_encode($data), $encryptedSubject, $device['devicepublickey'], OPENSSL_PKCS1_PADDING)) {
			$error = openssl_error_string();
			$this->log->error($error, ['app' => 'notifications']);
			$this->printInfo('Error while encrypting data: "' . $error . '"');
			throw new \InvalidArgumentException('Failed to encrypt message for device');
		}

		if (openssl_sign($encryptedSubject, $signature, $userKey->getPrivate(), OPENSSL_ALGO_SHA512)) {
			$this->printInfo('Signed encrypted push subject');
		} else {
			$this->printInfo('Failed to signed encrypted push subject');
		}
		$base64EncryptedSubject = base64_encode($encryptedSubject);
		$base64Signature = base64_encode($signature);

		return [
			'deviceIdentifier' => $device['deviceidentifier'],
			'pushTokenHash' => $device['pushtokenhash'],
			'subject' => $base64EncryptedSubject,
			'signature' => $base64Signature,
			'priority' => $priority,
			'type' => $type,
		];
	}

	/**
	 * json_encode is messing with multibyte characters a lot,
	 * replacing them with something along "\u1234".
	 * The data length in our encryption is a hard limit, but we don't want to
	 * make non-utf8 subjects unnecessary short. So this function tries to cut
	 * it off first.
	 * If that is not enough it always cuts off 5 characters in multibyte-safe
	 * fashion until the json_encode-d string is shorter then the given limit.
	 *
	 * @param string $subject
	 * @param int $dataLength
	 * @return string
	 */
	protected function shortenJsonEncodedMultibyte(string $subject, int $dataLength): string {
		$temp = mb_substr($subject, 0, $dataLength);
		while (strlen(json_encode($temp)) > $dataLength) {
			$temp = mb_substr($temp, 0, -5);
		}
		return $temp;
	}

	/**
	 * @param Key $userKey
	 * @param array $device
	 * @param int $id
	 * @return array
	 * @throws InvalidTokenException
	 * @throws \InvalidArgumentException
	 */
	protected function encryptAndSignDelete(Key $userKey, array $device, int $id): array {
		if ($id === 0) {
			$data = [
				'delete-all' => true,
			];
		} else {
			$data = [
				'nid' => $id,
				'delete' => true,
			];
		}

		if (!openssl_public_encrypt(json_encode($data), $encryptedSubject, $device['devicepublickey'], OPENSSL_PKCS1_PADDING)) {
			$this->log->error(openssl_error_string(), ['app' => 'notifications']);
			throw new \InvalidArgumentException('Failed to encrypt message for device');
		}

		openssl_sign($encryptedSubject, $signature, $userKey->getPrivate(), OPENSSL_ALGO_SHA512);
		$base64EncryptedSubject = base64_encode($encryptedSubject);
		$base64Signature = base64_encode($signature);

		return [
			'deviceIdentifier' => $device['deviceidentifier'],
			'pushTokenHash' => $device['pushtokenhash'],
			'subject' => $base64EncryptedSubject,
			'signature' => $base64Signature,
			'priority' => 'normal',
			'type' => 'background',
		];
	}

	/**
	 * @param string $uid
	 * @return array[]
	 */
	protected function getDevicesForUser(string $uid): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from('notifications_pushtokens')
			->where($query->expr()->eq('uid', $query->createNamedParameter($uid)));

		$result = $query->execute();
		$devices = $result->fetchAll();
		$result->closeCursor();

		return $devices;
	}

	/**
	 * @param int $tokenId
	 * @return bool
	 */
	protected function deletePushToken(int $tokenId): bool {
		$query = $this->db->getQueryBuilder();
		$query->delete('notifications_pushtokens')
			->where($query->expr()->eq('token', $query->createNamedParameter($tokenId, IQueryBuilder::PARAM_INT)));

		return $query->execute() !== 0;
	}

	/**
	 * @param string $deviceIdentifier
	 * @return bool
	 */
	protected function deletePushTokenByDeviceIdentifier(string $deviceIdentifier): bool {
		$query = $this->db->getQueryBuilder();
		$query->delete('notifications_pushtokens')
			->where($query->expr()->eq('deviceidentifier', $query->createNamedParameter($deviceIdentifier)));

		return $query->execute() !== 0;
	}
}
