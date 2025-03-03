<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OC\Authentication\Token\IProvider;
use OC\Security\IdentityProof\Key;
use OC\Security\IdentityProof\Manager;
use OCA\Notifications\AppInfo\Application;
use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Authentication\Exceptions\InvalidTokenException;
use OCP\Authentication\Token\IToken;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IUser;
use OCP\L10N\IFactory;
use OCP\Notification\AlreadyProcessedException;
use OCP\Notification\IManager as INotificationManager;
use OCP\Notification\IncompleteParsedNotificationException;
use OCP\Notification\INotification;
use OCP\Security\ISecureRandom;
use OCP\UserStatus\IManager as IUserStatusManager;
use OCP\UserStatus\IUserStatus;
use OCP\Util;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Push {
	protected ICache $cache;
	protected ?OutputInterface $output = null;
	/**
	 * @psalm-var array<string, list<string>>
	 */
	protected array $payloadsToSend = [];
	protected bool $deferPreparing = false;
	protected bool $deferPayloads = false;
	/**
	 * @var array[] $userId => $appId => $notificationIds
	 * @psalm-var array<string|int, array<string, list<int>>>
	 */
	protected array $deletesToPush = [];
	/**
	 * @psalm-var array<string|int, bool>
	 */
	protected array $deleteAllsToPush = [];
	/** @var INotification[] */
	protected array $notificationsToPush = [];

	/**
	 * @psalm-var array<string, ?IUserStatus>
	 */
	protected array $userStatuses = [];
	/**
	 * @psalm-var array<string, list<array{id: int, uid: string, token: int, deviceidentifier: string, devicepublickey: string, devicepublickeyhash: string, pushtokenhash: string, proxyserver: string, apptype: string}>>
	 */
	protected array $userDevices = [];
	/** @var string[] */
	protected array $loadDevicesForUsers = [];
	/** @var string[] */
	protected array $loadStatusForUsers = [];

	public function __construct(
		protected IDBConnection $db,
		protected INotificationManager $notificationManager,
		protected IConfig $config,
		protected IProvider $tokenProvider,
		protected Manager $keyManager,
		protected IClientService $clientService,
		ICacheFactory $cacheFactory,
		protected IUserStatusManager $userStatusManager,
		protected IFactory $l10nFactory,
		protected ITimeFactory $timeFactory,
		protected ISecureRandom $random,
		protected LoggerInterface $log,
	) {
		$this->cache = $cacheFactory->createDistributed('pushtokens');
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
		$this->deferPreparing = true;
		$this->deferPayloads = true;
	}

	public function flushPayloads(): void {
		$this->deferPreparing = false;

		if (!empty($this->loadDevicesForUsers)) {
			$this->loadDevicesForUsers = array_unique($this->loadDevicesForUsers);
			$missingDevicesFor = array_diff($this->loadDevicesForUsers, array_keys($this->userDevices));
			$newUserDevices = $this->getDevicesForUsers($missingDevicesFor);
			foreach ($missingDevicesFor as $userId) {
				$this->userDevices[$userId] = $newUserDevices[$userId] ?? [];
			}
			$this->loadDevicesForUsers = [];
		}

		if (!empty($this->loadStatusForUsers)) {
			$this->loadStatusForUsers = array_unique($this->loadStatusForUsers);
			$missingStatusFor = array_diff($this->loadStatusForUsers, array_keys($this->userStatuses));
			$newUserStatuses = $this->userStatusManager->getUserStatuses($missingStatusFor);
			foreach ($missingStatusFor as $userId) {
				$this->userStatuses[$userId] = $newUserStatuses[$userId] ?? null;
			}
			$this->loadStatusForUsers = [];
		}

		if (!empty($this->notificationsToPush)) {
			foreach ($this->notificationsToPush as $id => $notification) {
				$this->pushToDevice($id, $notification);
			}
			$this->notificationsToPush = [];
		}

		if (!empty($this->deleteAllsToPush)) {
			foreach ($this->deleteAllsToPush as $userId => $bool) {
				$this->pushDeleteToDevice((string)$userId, null);
			}
			$this->deleteAllsToPush = [];
		}

		if (!empty($this->deletesToPush)) {
			foreach ($this->deletesToPush as $userId => $data) {
				foreach ($data as $client => $notificationIds) {
					if ($client === 'talk') {
						$this->pushDeleteToDevice((string)$userId, $notificationIds, $client);
					} else {
						foreach ($notificationIds as $notificationId) {
							$this->pushDeleteToDevice((string)$userId, [$notificationId], $client);
						}
					}
				}
			}
			$this->deletesToPush = [];
		}

		$this->deferPayloads = false;
		$this->sendNotificationsToProxies();
	}

	/**
	 * @param array $devices
	 * @psalm-param $devices list<array{id: int, uid: string, token: int, deviceidentifier: string, devicepublickey: string, devicepublickeyhash: string, pushtokenhash: string, proxyserver: string, apptype: string}>
	 * @param string $app
	 * @return array
	 * @psalm-return list<array{id: int, uid: string, token: int, deviceidentifier: string, devicepublickey: string, devicepublickeyhash: string, pushtokenhash: string, proxyserver: string, apptype: string}>
	 */
	public function filterDeviceList(array $devices, string $app): array {
		$isTalkNotification = \in_array($app, ['spreed', 'talk', 'admin_notification_talk'], true);

		$talkDevices = array_filter($devices, static fn ($device) => $device['apptype'] === 'talk');
		$otherDevices = array_filter($devices, static fn ($device) => $device['apptype'] !== 'talk');

		$this->printInfo('Identified ' . count($talkDevices) . ' Talk devices and ' . count($otherDevices) . ' others.');

		if (!$isTalkNotification) {
			if (empty($otherDevices)) {
				// We only send file notifications to the files app.
				// If you don't have such a device, bye!
				return [];
			}
			return $otherDevices;
		}

		if (empty($talkDevices)) {
			// If you don't have a talk device,
			// we fall back to the files app.
			return $otherDevices;
		}
		return $talkDevices;
	}

	public function pushToDevice(int $id, INotification $notification, ?OutputInterface $output = null): void {
		if (!$this->config->getSystemValueBool('has_internet_connection', true)) {
			$this->printInfo('<error>Internet connectivity is disabled in configuration file - no push notifications will be sent</error>');

			return;
		}

		if ($this->deferPreparing) {
			$this->notificationsToPush[$id] = clone $notification;
			$this->loadDevicesForUsers[] = $notification->getUser();
			$this->loadStatusForUsers[] = $notification->getUser();
			return;
		}

		$user = $this->createFakeUserObject($notification->getUser());

		if (!array_key_exists($notification->getUser(), $this->userStatuses)) {
			$userStatus = $this->userStatusManager->getUserStatuses([
				$notification->getUser(),
			]);

			$this->userStatuses[$notification->getUser()] = $userStatus[$notification->getUser()] ?? null;
		}

		if (isset($this->userStatuses[$notification->getUser()])) {
			$userStatus = $this->userStatuses[$notification->getUser()];
			if ($userStatus instanceof IUserStatus
				&& $userStatus->getStatus() === IUserStatus::DND
				&& !$notification->isPriorityNotification()) {
				$this->printInfo('<error>User status is set to DND - no push notifications will be sent</error>');
				return;
			}
		}

		if (!array_key_exists($notification->getUser(), $this->userDevices)) {
			$devices = $this->getDevicesForUser($notification->getUser());
			$this->userDevices[$notification->getUser()] = $devices;
		} else {
			$devices = $this->userDevices[$notification->getUser()];
		}

		if (empty($devices)) {
			$this->printInfo('<comment>No devices found for user</comment>');
			return;
		}

		if (!$notification->isValidParsed()) {
			$language = $this->l10nFactory->getUserLanguage($user);
			$this->printInfo('Language is set to ' . $language);

			try {
				$this->notificationManager->setPreparingPushNotification(true);
				$notification = $this->notificationManager->prepare($notification, $language);
			} catch (AlreadyProcessedException|IncompleteParsedNotificationException|\InvalidArgumentException $e) {
				// FIXME remove \InvalidArgumentException in Nextcloud 39
				$this->printInfo('Error when preparing notification for push: ' . get_class($e));
				return;
			} finally {
				$this->notificationManager->setPreparingPushNotification(false);
			}
		}

		$userKey = $this->keyManager->getKey($user);

		$this->printInfo('Private user key size: ' . strlen($userKey->getPrivate()));
		$this->printInfo('Public user key size: ' . strlen($userKey->getPublic()));


		$this->printInfo('');
		$this->printInfo('Found ' . count($devices) . ' devices registered for push notifications');
		$isTalkNotification = \in_array($notification->getApp(), ['spreed', 'talk', 'admin_notification_talk'], true);
		$devices = $this->filterDeviceList($devices, $notification->getApp());
		if (empty($devices)) {
			$this->printInfo('<comment>No devices left after filtering</comment>');
			return;
		}
		$this->printInfo('Trying to push to ' . count($devices) . ' devices');

		// We don't push to devices that are older than 60 days
		$maxAge = time() - 60 * 24 * 60 * 60;

		foreach ($devices as $device) {
			$device['token'] = (int)$device['token'];
			$this->printInfo('');
			$this->printInfo('Device token: ' . $device['token']);

			if (!$this->validateToken($device['token'], $maxAge)) {
				// Token does not exist anymore
				continue;
			}

			try {
				$payload = json_encode($this->encryptAndSign($userKey, $device, $id, $notification, $isTalkNotification), JSON_THROW_ON_ERROR);

				$proxyServer = rtrim($device['proxyserver'], '/');
				if (!isset($this->payloadsToSend[$proxyServer])) {
					$this->payloadsToSend[$proxyServer] = [];
				}
				$this->payloadsToSend[$proxyServer][] = $payload;
			} catch (\JsonException $e) {
				$this->log->error('JSON error while encoding push notification: ' . $e->getMessage(), ['exception' => $e]);
			} catch (\InvalidArgumentException) {
				// Failed to encrypt message for device: public key is invalid
				$this->deletePushToken($device['token']);
			}
		}
		$this->printInfo('');

		if (!$this->deferPayloads) {
			$this->sendNotificationsToProxies();
		}
	}

	/**
	 * @param string $userId
	 * @param ?int[] $notificationIds
	 * @param string $app
	 */
	public function pushDeleteToDevice(string $userId, ?array $notificationIds, string $app = ''): void {
		if (!$this->config->getSystemValueBool('has_internet_connection', true)) {
			return;
		}

		if ($this->deferPreparing) {
			if ($notificationIds === null) {
				$this->deleteAllsToPush[$userId] = true;
				if (isset($this->deletesToPush[$userId])) {
					unset($this->deletesToPush[$userId]);
				}
			} else {
				if (isset($this->deleteAllsToPush[$userId])) {
					return;
				}

				$isTalkNotification = \in_array($app, ['spreed', 'talk', 'admin_notification_talk'], true);
				$clientGroup = $isTalkNotification ? 'talk' : 'files';

				if (!isset($this->deletesToPush[$userId])) {
					$this->deletesToPush[$userId] = [];
				}
				if (!isset($this->deletesToPush[$userId][$clientGroup])) {
					$this->deletesToPush[$userId][$clientGroup] = [];
				}

				foreach ($notificationIds as $notificationId) {
					$this->deletesToPush[$userId][$clientGroup][] = $notificationId;
				}
			}
			$this->loadDevicesForUsers[] = $userId;
			return;
		}

		$deleteAll = $notificationIds === null;

		$user = $this->createFakeUserObject($userId);

		if (!array_key_exists($userId, $this->userDevices)) {
			$devices = $this->getDevicesForUser($userId);
			$this->userDevices[$userId] = $devices;
		} else {
			$devices = $this->userDevices[$userId];
		}

		if (!$deleteAll) {
			// Only filter when it's not delete-all
			$devices = $this->filterDeviceList($devices, $app);
		}
		if (empty($devices)) {
			return;
		}

		// We don't push to devices that are older than 60 days
		$maxAge = time() - 60 * 24 * 60 * 60;

		$userKey = $this->keyManager->getKey($user);
		foreach ($devices as $device) {
			$device['token'] = (int)$device['token'];
			if (!$this->validateToken($device['token'], $maxAge)) {
				// Token does not exist anymore
				continue;
			}

			try {
				$proxyServer = rtrim($device['proxyserver'], '/');
				if (!isset($this->payloadsToSend[$proxyServer])) {
					$this->payloadsToSend[$proxyServer] = [];
				}

				if ($deleteAll) {
					$data = $this->encryptAndSignDelete($userKey, $device, null);
					try {
						$this->payloadsToSend[$proxyServer][] = json_encode($data['payload'], JSON_THROW_ON_ERROR);
					} catch (\JsonException $e) {
						$this->log->error('JSON error while encoding push notification: ' . $e->getMessage(), ['exception' => $e]);
					}
				} else {
					$temp = $notificationIds;

					while (!empty($temp)) {
						$data = $this->encryptAndSignDelete($userKey, $device, $temp);
						$temp = $data['remaining'];
						try {
							$this->payloadsToSend[$proxyServer][] = json_encode($data['payload'], JSON_THROW_ON_ERROR);
						} catch (\JsonException $e) {
							$this->log->error('JSON error while encoding push notification: ' . $e->getMessage(), ['exception' => $e]);
						}
					}
				}
			} catch (\InvalidArgumentException) {
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

		if (!$this->notificationManager->isFairUseOfFreePushService()) {
			/**
			 * We want to keep offering our push notification service for free, but large
			 * users overload our infrastructure. For this reason we have to rate-limit the
			 * use of push notifications. If you need this feature, consider using Nextcloud Enterprise.
			 */
			return;
		}

		$subscriptionAwareServer = rtrim($this->config->getAppValue(Application::APP_ID, 'subscription_aware_server', 'https://push-notifications.nextcloud.com'), '/');
		if ($subscriptionAwareServer === 'https://push-notifications.nextcloud.com') {
			$subscriptionKey = $this->config->getAppValue('support', 'subscription_key');
		} else {
			$subscriptionKey = $this->config->getAppValue(Application::APP_ID, 'push_subscription_key');
			if ($subscriptionKey === '') {
				$subscriptionKey = $this->createPushSubscriptionKey();
				$this->config->setAppValue(Application::APP_ID, 'push_subscription_key', $subscriptionKey);
			}
		}

		$client = $this->clientService->newClient();
		foreach ($pushNotifications as $proxyServer => $notifications) {
			try {
				$requestData = [
					'body' => [
						'notifications' => $notifications,
					],
				];

				if ($subscriptionKey !== '' && $proxyServer === $subscriptionAwareServer) {
					$requestData['headers']['X-Nextcloud-Subscription-Key'] = $subscriptionKey;
				}

				$response = $client->post($proxyServer . '/notifications', $requestData);
				$status = $response->getStatusCode();
				$body = (string)$response->getBody();
				try {
					$bodyData = json_decode($body, true);
				} catch (\JsonException) {
					$bodyData = null;
				}
			} catch (ClientException $e) {
				// Server responded with 4xx (400 Bad Request mostlikely)
				$response = $e->getResponse();
				$status = $response->getStatusCode();
				$body = $response->getBody()->getContents();
				try {
					$bodyData = json_decode($body, true);
				} catch (\JsonException) {
					$bodyData = null;
				}
			} catch (ServerException $e) {
				// Server responded with 5xx
				$response = $e->getResponse();
				$body = $response->getBody()->getContents();
				$error = \is_string($body) ? $body : ('no reason given (' . $response->getStatusCode() . ')');

				$this->log->debug('Could not send notification to push server [{url}]: {error}', [
					'error' => $error,
					'url' => $proxyServer,
					'app' => 'notifications',
				]);

				$this->printInfo('<error>Could not send notification to push server [' . $proxyServer . ']: ' . $error . '</error>');
				continue;
			} catch (\Exception $e) {
				$this->log->error($e->getMessage(), [
					'exception' => $e,
				]);

				$error = $e->getMessage() ?: 'no reason given';
				$this->printInfo('<error>Could not send notification to push server [' . $e::class . ']: ' . $error . '</error>');
				continue;
			}

			if (is_array($bodyData) && array_key_exists('unknown', $bodyData) && array_key_exists('failed', $bodyData)) {
				if (is_array($bodyData['unknown'])) {
					// Proxy returns null when the array is empty
					foreach ($bodyData['unknown'] as $unknownDevice) {
						$this->printInfo('<comment>Deleting device because it is unknown by the push server: ' . $unknownDevice . '</comment>');
						$this->deletePushTokenByDeviceIdentifier($unknownDevice);
					}
				}

				if ($bodyData['failed'] !== 0) {
					$this->printInfo('<comment>Push notification sent, but ' . $bodyData['failed'] . ' failed</comment>');
				} else {
					$this->printInfo('<info>Push notification sent successfully</info>');
				}
			} elseif ($status !== Http::STATUS_OK) {
				if ($status === Http::STATUS_TOO_MANY_REQUESTS) {
					$this->config->setAppValue(Application::APP_ID, 'rate_limit_reached', (string)$this->timeFactory->getTime());
				}
				$error = $body && $bodyData === null ? $body : 'no reason given';
				$this->printInfo('<error>Could not send notification to push server [' . $proxyServer . ']: ' . $error . '</error>');
				$this->log->warning('Could not send notification to push server [{url}]: {error}', [
					'error' => $error,
					'url' => $proxyServer,
					'app' => 'notifications',
				]);
			} else {
				$error = $body && $bodyData === null ? $body : 'no reason given';
				$this->printInfo('<comment>Push notification sent but response was not parsable, using an outdated push proxy? [' . $proxyServer . ']: ' . $error . '</comment>');
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

		if ($age === null) {
			try {
				// Check if the token is still valid...
				$token = $this->tokenProvider->getTokenById($tokenId);
				$type = $this->callSafelyForToken($token, 'getType');
				if ($type === IToken::WIPE_TOKEN) {
					// Token does not exist any more, should drop the push device entry
					$this->printInfo('Device token is marked for remote wipe');
					$this->deletePushToken($tokenId);
					$this->cache->set('t' . $tokenId, 0, 600);
					return false;
				}

				$age = $token->getLastCheck();
				$lastActivity = $this->callSafelyForToken($token, 'getLastActivity');
				if ($lastActivity) {
					$age = max($age, $lastActivity);
				}
				$this->cache->set('t' . $tokenId, $age, 600);
			} catch (InvalidTokenException) {
				// Token does not exist any more, should drop the push device entry
				$this->printInfo('<error>InvalidTokenException is thrown</error>');
				$this->deletePushToken($tokenId);
				$this->cache->set('t' . $tokenId, 0, 600);
				return false;
			}
		}

		if ($age > $maxAge) {
			$this->printInfo('Device token is valid');
			return true;
		}

		$this->printInfo('<comment>Device token "last checked" is older than 60 days: ' . $age . '</comment>');
		return false;
	}

	/**
	 * The functions are not part of public API so we are a bit more careful
	 * @param IToken $token
	 * @param 'getLastActivity'|'getType' $method
	 * @return int|null
	 */
	protected function callSafelyForToken(IToken $token, string $method): ?int {
		if (method_exists($token, $method) || method_exists($token, '__call')) {
			try {
				$result = $token->$method();
				if (is_int($result)) {
					return $result;
				}
			} catch (\BadFunctionCallException) {
			}
		}
		return null;
	}

	/**
	 * @param Key $userKey
	 * @param array $device
	 * @param int $id
	 * @param INotification $notification
	 * @param bool $isTalkNotification
	 * @return array
	 * @psalm-return array{deviceIdentifier: string, pushTokenHash: string, subject: string, signature: string, priority: string, type: string}
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
		// Also, subtract two for encapsulating quotes will be added.
		$maxDataLength = 200 - strlen(json_encode($data)) - 2;
		$data['subject'] = Util::shortenMultibyteString($notification->getParsedSubject(), $maxDataLength);
		if ($notification->getParsedSubject() !== $data['subject']) {
			$data['subject'] .= '…';
		}

		if ($isTalkNotification) {
			$priority = 'high';
			$type = $data['type'] === 'call' ? 'voip' : 'alert';
		} elseif ($data['app'] === 'twofactor_nextcloud_notification' || $data['app'] === 'phonetrack') {
			$priority = 'high';
			$type = 'alert';
		} else {
			$priority = 'normal';
			$type = 'alert';
		}

		$this->printInfo('Device public key size: ' . strlen($device['devicepublickey']));
		$this->printInfo('Data to encrypt is: ' . json_encode($data));

		if (!openssl_public_encrypt(json_encode($data), $encryptedSubject, $device['devicepublickey'], OPENSSL_PKCS1_PADDING)) {
			$error = openssl_error_string();
			$this->log->error($error, ['app' => 'notifications']);
			$this->printInfo('<error>Error while encrypting data: "' . $error . '"</error>');
			throw new \InvalidArgumentException('Failed to encrypt message for device');
		}

		if (openssl_sign($encryptedSubject, $signature, $userKey->getPrivate(), OPENSSL_ALGO_SHA512)) {
			$this->printInfo('Signed encrypted push subject');
		} else {
			$this->printInfo('<error>Failed to signed encrypted push subject</error>');
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
	 * @param Key $userKey
	 * @param array $device
	 * @param ?int[] $ids
	 * @return array
	 * @psalm-return array{remaining: list<int>, payload: array{deviceIdentifier: string, pushTokenHash: string, subject: string, signature: string, priority: string, type: string}}
	 * @throws InvalidTokenException
	 * @throws \InvalidArgumentException
	 */
	protected function encryptAndSignDelete(Key $userKey, array $device, ?array $ids): array {
		$remainingIds = [];
		if ($ids === null) {
			$data = [
				'delete-all' => true,
			];
		} elseif (count($ids) === 1) {
			$data = [
				'nid' => array_pop($ids),
				'delete' => true,
			];
		} else {
			$remainingIds = array_splice($ids, 10);
			$data = [
				'nids' => $ids,
				'delete-multiple' => true,
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
			'remaining' => $remainingIds,
			'payload' => [
				'deviceIdentifier' => $device['deviceidentifier'],
				'pushTokenHash' => $device['pushtokenhash'],
				'subject' => $base64EncryptedSubject,
				'signature' => $base64Signature,
				'priority' => 'normal',
				'type' => 'background',
			]
		];
	}

	/**
	 * @param string $uid
	 * @return array[]
	 * @psalm-return list<array{id: int, uid: string, token: int, deviceidentifier: string, devicepublickey: string, devicepublickeyhash: string, pushtokenhash: string, proxyserver: string, apptype: string}>
	 */
	protected function getDevicesForUser(string $uid): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from('notifications_pushhash')
			->where($query->expr()->eq('uid', $query->createNamedParameter($uid)));

		$result = $query->executeQuery();
		$devices = $result->fetchAll();
		$result->closeCursor();

		return $devices;
	}

	/**
	 * @param string[] $userIds
	 * @return array[]
	 * @psalm-return array<string, list<array{id: int, uid: string, token: int, deviceidentifier: string, devicepublickey: string, devicepublickeyhash: string, pushtokenhash: string, proxyserver: string, apptype: string}>>
	 */
	protected function getDevicesForUsers(array $userIds): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from('notifications_pushhash')
			->where($query->expr()->in('uid', $query->createNamedParameter($userIds, IQueryBuilder::PARAM_STR_ARRAY)));

		$devices = [];
		$result = $query->executeQuery();
		while ($row = $result->fetch()) {
			if (!isset($devices[$row['uid']])) {
				$devices[$row['uid']] = [];
			}
			$devices[$row['uid']][] = $row;
		}

		$result->closeCursor();

		return $devices;
	}

	/**
	 * @param int $tokenId
	 * @return bool
	 */
	protected function deletePushToken(int $tokenId): bool {
		$query = $this->db->getQueryBuilder();
		$query->delete('notifications_pushhash')
			->where($query->expr()->eq('token', $query->createNamedParameter($tokenId, IQueryBuilder::PARAM_INT)));

		return $query->executeStatement() !== 0;
	}

	/**
	 * @param string $deviceIdentifier
	 * @return bool
	 */
	protected function deletePushTokenByDeviceIdentifier(string $deviceIdentifier): bool {
		$query = $this->db->getQueryBuilder();
		$query->delete('notifications_pushhash')
			->where($query->expr()->eq('deviceidentifier', $query->createNamedParameter($deviceIdentifier)));

		return $query->executeStatement() !== 0;
	}

	protected function createFakeUserObject(string $userId): IUser {
		return new FakeUser($userId);
	}

	protected function createPushSubscriptionKey(): string {
		$key = $this->random->generate(25, ISecureRandom::CHAR_ALPHANUMERIC);
		return implode('-', str_split($key, 5));
	}
}
