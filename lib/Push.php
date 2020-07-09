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


use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\Security\IdentityProof\Key;
use OC\Security\IdentityProof\Manager;
use OCP\AppFramework\Http;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager as INotificationManager;
use OCP\Notification\INotification;

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
	/** @var ILogger */
	protected $log;

	public function __construct(IDBConnection $connection, INotificationManager $notificationManager, IConfig $config, IProvider $tokenProvider, Manager $keyManager, IUserManager $userManager, IClientService $clientService, ILogger $log) {
		$this->db = $connection;
		$this->notificationManager = $notificationManager;
		$this->config = $config;
		$this->tokenProvider = $tokenProvider;
		$this->keyManager = $keyManager;
		$this->userManager = $userManager;
		$this->clientService = $clientService;
		$this->log = $log;
	}

	public function pushToDevice(int $id, INotification $notification): void {
		$user = $this->userManager->get($notification->getUser());
		if (!($user instanceof IUser)) {
			return;
		}

		$devices = $this->getDevicesForUser($notification->getUser());
		if (empty($devices)) {
			return;
		}

		$language = $this->config->getSystemValue('force_language', false);
		$language = \is_string($language) ? $language : $this->config->getUserValue($notification->getUser(), 'core', 'lang', null);
		$language = $language ?? $this->config->getSystemValue('default_language', 'en');
		try {
			$this->notificationManager->setPreparingPushNotification(true);
			$notification = $this->notificationManager->prepare($notification, $language);
		} catch (\InvalidArgumentException $e) {
			return;
		} finally {
			$this->notificationManager->setPreparingPushNotification(false);
		}

		$userKey = $this->keyManager->getKey($user);

		$isTalkNotification = \in_array($notification->getApp(), ['spreed', 'talk'], true);
		$talkApps = array_filter($devices, function ($device) {
			return $device['apptype'] === 'talk';
		});
		$hasTalkApps = !empty($talkApps);

		foreach ($devices as $device) {
			if (!$isTalkNotification && $device['apptype'] === 'talk') {
				// The iOS app can not kill notifications,
				// therefor we should only send relevant notifications to the Talk
				// app, so it does not pollute the notifications bar with useless
				// notifications, especially when the Sync client app is also installed.
				continue;
			}
			if ($isTalkNotification && $hasTalkApps && $device['apptype'] !== 'talk') {
				// Similar to the previous case, we also don't send Talk notifications
				// to the Sync client app, when there is a Talk app installed. We only
				// do this, when you don't have a Talk app on your device, so you still
				// get the push notification.
				continue;
			}

			try {
				$payload = json_encode($this->encryptAndSign($userKey, $device, $id, $notification, $isTalkNotification));

				$proxyServer = rtrim($device['proxyserver'], '/');
				if (!isset($this->payloadsToSend[$proxyServer])) {
					$this->payloadsToSend[$proxyServer] = [];
				}
				$pushNotifications[$proxyServer][] = $payload;
			} catch (InvalidTokenException $e) {
				// Token does not exist anymore, should drop the push device entry
				$this->deletePushToken($device['token']);
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
		$user = $this->userManager->get($userId);
		if (!($user instanceof IUser)) {
			return;
		}

		$devices = $this->getDevicesForUser($userId);
		if (empty($devices)) {
			return;
		}

		$userKey = $this->keyManager->getKey($user);
		foreach ($devices as $device) {
			try {
				$payload = json_encode($this->encryptAndSignDelete($userKey, $device, $notificationId));

				$proxyServer = rtrim($device['proxyserver'], '/');
				if (!isset($this->payloadsToSend[$proxyServer])) {
					$this->payloadsToSend[$proxyServer] = [];
				}
				$pushNotifications[$proxyServer][] = $payload;
			} catch (InvalidTokenException $e) {
				// Token does not exist anymore, should drop the push device entry
				$this->deletePushToken($device['token']);
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
			} catch (\Exception $e) {
				$this->log->logException($e, [
					'app' => 'notifications',
					'level' => $e->getCode() === Http::STATUS_BAD_REQUEST ? ILogger::INFO : ILogger::WARN,
				]);
				continue;
			}

			$status = $response->getStatusCode();
			if ($status === Http::STATUS_SERVICE_UNAVAILABLE && $this->config->getSystemValue('debug', false)) {
				$body = $response->getBody();
				$this->log->debug('Could not send notification to push server [{url}]: {error}',[
					'error' => \is_string($body) ? $body : 'no reason given',
					'url' => $proxyServer,
					'app' => 'notifications',
				]);
				continue;
			}

			$body = $response->getBody();
			$bodyData = json_decode($body, true);
			if ($status !== Http::STATUS_OK) {
				$this->log->error('Could not send notification to push server [{url}]: {error}',[
					'error' => \is_string($body) && $bodyData === null ? $body : 'no reason given',
					'url' => $proxyServer,
					'app' => 'notifications',
				]);
			}

			if (is_array($bodyData) && !empty($bodyData['unknown']) && is_array($bodyData['unknown'])) {
				foreach ($bodyData['unknown'] as $unknownDevice) {
					$this->deletePushTokenByDeviceIdentifier($unknownDevice);
				}
			}
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
		// Check if the token is still valid...
		$this->tokenProvider->getTokenById($device['token']);

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
		// Check if the token is still valid...
		$this->tokenProvider->getTokenById($device['token']);

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
