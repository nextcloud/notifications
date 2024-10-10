<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Notifications\Tests\Unit;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\Security\IdentityProof\Key;
use OC\Security\IdentityProof\Manager;
use OCA\Notifications\Push;
use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IUser;
use OCP\L10N\IFactory;
use OCP\Notification\IManager as INotificationManager;
use OCP\Notification\INotification;
use OCP\UserStatus\IManager as IUserStatusManager;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PushTest
 *
 * @package OCA\Notifications\Tests\Unit
 * @group DB
 */
class PushTest extends TestCase {
	/** @var IDBConnection */
	protected $db;
	/** @var INotificationManager|MockObject */
	protected $notificationManager;
	/** @var IConfig|MockObject */
	protected $config;
	/** @var IProvider|MockObject */
	protected $tokenProvider;
	/** @var Manager|MockObject */
	protected $keyManager;
	/** @var IClientService|MockObject */
	protected $clientService;
	/** @var ICacheFactory|MockObject */
	protected $cacheFactory;
	/** @var ICache|MockObject */
	protected $cache;
	/** @var IUserStatusManager|MockObject */
	protected $userStatusManager;
	/** @var IFactory|MockObject */
	protected $l10nFactory;
	/** @var ITimeFactory|MockObject */
	protected $timeFactory;
	/** @var LoggerInterface|MockObject */
	protected $logger;

	protected function setUp(): void {
		parent::setUp();

		$this->db = \OC::$server->getDatabaseConnection();
		$this->notificationManager = $this->createMock(INotificationManager::class);
		$this->config = $this->createMock(IConfig::class);
		$this->tokenProvider = $this->createMock(IProvider::class);
		$this->keyManager = $this->createMock(Manager::class);
		$this->clientService = $this->createMock(IClientService::class);
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->cache = $this->createMock(ICache::class);
		$this->userStatusManager = $this->createMock(IUserStatusManager::class);
		$this->l10nFactory = $this->createMock(IFactory::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->cacheFactory->method('createDistributed')
			->with('pushtokens')
			->willReturn($this->cache);
	}

	/**
	 * @param string[] $methods
	 * @return Push|MockObject
	 */
	protected function getPush(array $methods = []) {
		if (!empty($methods)) {
			return $this->getMockBuilder(Push::class)
				->setConstructorArgs([
					$this->db,
					$this->notificationManager,
					$this->config,
					$this->tokenProvider,
					$this->keyManager,
					$this->clientService,
					$this->cacheFactory,
					$this->userStatusManager,
					$this->l10nFactory,
					$this->timeFactory,
					$this->logger,
				])
				->setMethods($methods)
				->getMock();
		}

		return new Push(
			$this->db,
			$this->notificationManager,
			$this->config,
			$this->tokenProvider,
			$this->keyManager,
			$this->clientService,
			$this->cacheFactory,
			$this->userStatusManager,
			$this->l10nFactory,
			$this->logger
		);
	}

	public function testPushToDeviceNoInternet() {
		$push = $this->getPush(['createFakeUserObject']);

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(false);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');
		$push->expects($this->never())
			->method('createFakeUserObject');

		/** @var INotification|MockObject$notification */
		$notification = $this->createMock(INotification::class);

		$push->pushToDevice(23, $notification);
	}

	public function testPushToDeviceNoDevices() {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser']);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|MockObject $user */
		$user = $this->createMock(IUser::class);

		$push->expects($this->once())
			->method('createFakeUserObject')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([]);

		$push->pushToDevice(42, $notification);
	}

	public function testPushToDeviceNotPrepared() {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser']);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|MockObject $user */
		$user = $this->createMock(IUser::class);

		$push->expects($this->once())
			->method('createFakeUserObject')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 'token1',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('de');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'de')
			->willThrowException(new \InvalidArgumentException());

		$push->pushToDevice(1337, $notification);
	}

	public function testPushToDeviceInvalidToken() {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken']);
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|MockObject $user */
		$user = $this->createMock(IUser::class);

		$push->expects($this->once())
			->method('createFakeUserObject')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 23,
				'apptype' => 'other',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);


		/** @var Key|MockObject $key */
		$key = $this->createMock(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$this->tokenProvider->expects($this->once())
			->method('getTokenById')
			->willThrowException(new InvalidTokenException());

		$push->expects($this->never())
			->method('encryptAndSign');

		$push->expects($this->once())
			->method('deletePushToken')
			->with(23);

		$push->pushToDevice(2018, $notification);
	}

	public function testPushToDeviceEncryptionError() {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken', 'validateToken']);
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|MockObject $user */
		$user = $this->createMock(IUser::class);

		$push->expects($this->once())
			->method('createFakeUserObject')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 23,
				'apptype' => 'other',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key|MockObject $key */
		$key = $this->createMock(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->once())
			->method('validateToken')
			->willReturn(true);

		$push->expects($this->once())
			->method('encryptAndSign')
			->willThrowException(new \InvalidArgumentException());

		$push->expects($this->once())
			->method('deletePushToken')
			->with(23);

		$push->pushToDevice(1970, $notification);
	}
	public function testPushToDeviceNoFairUse() {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken', 'validateToken', 'deletePushTokenByDeviceIdentifier']);

		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|MockObject $user */
		$user = $this->createMock(IUser::class);

		$push->expects($this->once())
			->method('createFakeUserObject')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([
				[
					'proxyserver' => 'proxyserver',
					'token' => 16,
					'apptype' => 'other',
				],
			]);

		$this->config
			->method('getSystemValue')
			->with('debug', false)
			->willReturn(false);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key|MockObject $key */
		$key = $this->createMock(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->exactly(1))
			->method('validateToken')
			->willReturn(true);

		$push->expects($this->exactly(1))
			->method('encryptAndSign')
			->willReturn(['Payload']);

		$push->expects($this->never())
			->method('deletePushToken');

		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		$this->notificationManager->method('isFairUseOfFreePushService')
			->willReturn(false);

		$push->method('deletePushTokenByDeviceIdentifier')
			->with('123456');

		$push->pushToDevice(207787, $notification);
	}

	public function dataPushToDeviceSending() {
		return [
			[true],
			[false],
		];
	}

	/**
	 * @dataProvider dataPushToDeviceSending
	 * @param bool $isDebug
	 */
	public function testPushToDeviceSending($isDebug) {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken', 'validateToken', 'deletePushTokenByDeviceIdentifier']);

		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|MockObject $user */
		$user = $this->createMock(IUser::class);

		$push->expects($this->once())
			->method('createFakeUserObject')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([
				[
					'proxyserver' => 'proxyserver1',
					'token' => 16,
					'apptype' => 'other',
				],
				[
					'proxyserver' => 'proxyserver1/',
					'token' => 23,
					'apptype' => 'other',
				],
				[
					'proxyserver' => 'badrequest',
					'token' => 42,
					'apptype' => 'other',
				],
				[
					'proxyserver' => 'unavailable',
					'token' => 48,
					'apptype' => 'other',
				],
				[
					'proxyserver' => 'ok',
					'token' => 64,
					'apptype' => 'other',
				],
				[
					'proxyserver' => 'badrequest-with-devices',
					'token' => 128,
					'apptype' => 'other',
				],
			]);

		$this->config
			->method('getSystemValue')
			->with('debug', false)
			->willReturn($isDebug);

		$this->config
			->method('getAppValue')
			->willReturnMap([
				['notifications', 'subscription_aware_server', 'https://push-notifications.nextcloud.com', 'https://push-notifications.nextcloud.com'],
				['support', 'subscription_key', '', ''],
			]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key|MockObject $key */
		$key = $this->createMock(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->exactly(6))
			->method('validateToken')
			->willReturn(true);

		$push->expects($this->exactly(6))
			->method('encryptAndSign')
			->willReturn(['Payload']);

		$push->expects($this->never())
			->method('deletePushToken');

		/** @var IClient|MockObject $client */
		$client = $this->createMock(IClient::class);

		$this->clientService->expects($this->once())
			->method('newClient')
			->willReturn($client);

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		// Call 1
		/** @var ResponseInterface|MockObject $response1 */
		$response1 = $this->createMock(ResponseInterface::class);
		$response1->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_BAD_REQUEST);
		/** @var StreamInterface|MockObject $body1 */
		$body1 = $this->createMock(StreamInterface::class);
		$body1->expects($this->once())
			->method('getContents')
			->willReturn('');
		$response1->expects($this->once())
			->method('getBody')
			->willReturn($body1);
		$exception1 = $this->createMock(ClientException::class);
		$exception1->method('getResponse')
			->willReturn($response1);

		// Call 2
		/** @var ResponseInterface|MockObject $response1 */
		$response2 = $this->createMock(ResponseInterface::class);
		/** @var StreamInterface|MockObject $body2 */
		$body2 = $this->createMock(StreamInterface::class);
		$body2->expects($this->once())
			->method('getContents')
			->willReturn('Maintenance');
		$response2->expects($this->once())
			->method('getBody')
			->willReturn($body2);
		$exception2 = $this->createMock(ServerException::class);
		$exception2->method('getResponse')
			->willReturn($response2);


		// Call 3
		/** @var IResponse|MockObject $response1 */
		$response3 = $this->createMock(IResponse::class);
		$response3->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_OK);
		/** @var StreamInterface|MockObject $body3 */
		$body3 = $this->createMock(StreamInterface::class);
		$response3->method('getBody')
			->willReturn('');

		// Call 4
		/** @var ResponseInterface|MockObject $response1 */
		$response4 = $this->createMock(ResponseInterface::class);
		$response4->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_BAD_REQUEST);
		/** @var StreamInterface|MockObject $body4 */
		$body4 = $this->createMock(StreamInterface::class);
		$body4->expects($this->once())
			->method('getContents')
			->willReturn(json_encode([
				'failed' => 1,
				'unknown' => [
					'123456'
				]
			]));
		$response4->expects($this->once())
			->method('getBody')
			->willReturn($body4);
		$exception4 = $this->createMock(ClientException::class);
		$exception4->method('getResponse')
			->willReturn($response4);

		$exception0 = new \Exception();
		$client->expects($this->exactly(5))
			->method('post')
			->withConsecutive(
				[
					'proxyserver1/notifications',
					[
						'body' => [
							'notifications' => ['["Payload"]', '["Payload"]'],
						]
					]
				],
				[
					'badrequest/notifications',
					[
						'body' => [
							'notifications' => ['["Payload"]'],
						]
					]
				],
				[
					'unavailable/notifications',
					[
						'body' => [
							'notifications' => ['["Payload"]'],
						]
					],
				],
				[
					'ok/notifications',
					[
						'body' => [
							'notifications' => ['["Payload"]'],
						]
					],
				]
			)
			->willReturnOnConsecutiveCalls(
				$this->throwException($exception0),
				$this->throwException($exception1),
				$this->throwException($exception2),
				$response3,
				$this->throwException($exception4),
			);

		$this->logger->expects($this->atLeastOnce())
			->method('error')
			->with($exception0->getMessage(), [
				'exception' => $exception0,
			]);
		$this->logger->expects($this->once())
			->method('warning')
			->with('Could not send notification to push server [{url}]: {error}', [
				'error' => 'no reason given',
				'url' => 'badrequest',
				'app' => 'notifications',
			]);
		$this->logger->expects($this->once())
			->method('debug')
			->with('Could not send notification to push server [{url}]: {error}', [
				'error' => 'Maintenance',
				'url' => 'unavailable',
				'app' => 'notifications',
			]);

		$this->notificationManager->method('isFairUseOfFreePushService')
			->willReturn(true);

		$push->method('deletePushTokenByDeviceIdentifier')
			->with('123456');

		$push->pushToDevice(207787, $notification);
	}

	public function dataPushToDeviceTalkNotification() {
		return [
			[['nextcloud'], false, 0],
			[['nextcloud'], true, 0],
			[['nextcloud', 'talk'], false, 0],
			[['nextcloud', 'talk'], true, 1],
			[['talk', 'nextcloud'], false, 1],
			[['talk', 'nextcloud'], true, 0],
			[['talk'], false, null],
			[['talk'], true, 0],
		];
	}

	/**
	 * @dataProvider dataPushToDeviceTalkNotification
	 * @param string[] $deviceTypes
	 * @param bool $isTalkNotification
	 * @param int $pushedDevice
	 */
	public function testPushToDeviceTalkNotification(array $deviceTypes, $isTalkNotification, $pushedDevice) {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken', 'validateToken']);

		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		if ($isTalkNotification) {
			$notification->expects($this->any())
				->method('getApp')
				->willReturn('spreed');
		} else {
			$notification->expects($this->any())
				->method('getApp')
				->willReturn('notifications');
		}

		/** @var IUser|MockObject $user */
		$user = $this->createMock(IUser::class);

		$push->expects($this->once())
			->method('createFakeUserObject')
			->with('valid')
			->willReturn($user);

		$devices = [];
		foreach ($deviceTypes as $deviceType) {
			$devices[] = [
				'proxyserver' => 'proxyserver',
				'token' => strlen($deviceType),
				'apptype' => $deviceType,
			];
		}
		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn($devices);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key|MockObject $key */
		$key = $this->createMock(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		if ($pushedDevice === null) {
			$push->expects($this->never())
				->method('validateToken');

			$push->expects($this->never())
				->method('encryptAndSign');

			$this->clientService->expects($this->never())
				->method('newClient');
		} else {
			$push->expects($this->exactly(1))
				->method('validateToken')
				->willReturn(true);

			$push->expects($this->exactly(1))
				->method('encryptAndSign')
				->with($this->anything(), $devices[$pushedDevice], $this->anything(), $this->anything(), $isTalkNotification)
				->willReturn(['Payload']);

			/** @var IClient|MockObject $client */
			$client = $this->createMock(IClient::class);

			$this->clientService->expects($this->once())
				->method('newClient')
				->willReturn($client);

			/** @var IResponse|MockObject $response1 */
			$response = $this->createMock(IResponse::class);
			$response->expects($this->once())
				->method('getStatusCode')
				->willReturn(Http::STATUS_BAD_REQUEST);
			$response->expects($this->once())
				->method('getBody')
				->willReturn('');
			$client->expects($this->once())
				->method('post')
				->with('proxyserver/notifications', [
					'body' => [
						'notifications' => ['["Payload"]'],
					],
				])
				->willReturn($response);
		}

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		$this->config
			->method('getAppValue')
			->willReturnMap([
				['notifications', 'subscription_aware_server', 'https://push-notifications.nextcloud.com', 'https://push-notifications.nextcloud.com'],
				['support', 'subscription_key', '', ''],
			]);

		$this->notificationManager->method('isFairUseOfFreePushService')
			->willReturn(true);

		$push->pushToDevice(200718, $notification);
	}
}
