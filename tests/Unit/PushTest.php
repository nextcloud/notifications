<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\Authentication\Token\PublicKeyToken;
use OC\Security\IdentityProof\Key;
use OC\Security\IdentityProof\Manager;
use OCA\Notifications\Push;
use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Authentication\Token\IToken as OCPIToken;
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
use OCP\Security\ISecureRandom;
use OCP\UserStatus\IManager as IUserStatusManager;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * Class PushTest
 *
 * @package OCA\Notifications\Tests\Unit
 * @group DB
 */
class PushTest extends TestCase {
	protected IDBConnection $db;
	protected INotificationManager&MockObject $notificationManager;
	protected IConfig&MockObject $config;
	protected IProvider&MockObject $tokenProvider;
	protected Manager&MockObject $keyManager;
	protected IClientService&MockObject $clientService;
	protected ICacheFactory&MockObject $cacheFactory;
	protected ICache&MockObject $cache;
	protected IUserStatusManager&MockObject $userStatusManager;
	protected IFactory&MockObject $l10nFactory;
	protected ITimeFactory&MockObject $timeFactory;
	protected ISecureRandom&MockObject $random;
	protected LoggerInterface&MockObject $logger;

	protected function setUp(): void {
		parent::setUp();

		$this->db = \OCP\Server::get(IDBConnection::class);
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
		$this->random = $this->createMock(ISecureRandom::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->cacheFactory->method('createDistributed')
			->with('pushtokens')
			->willReturn($this->cache);
	}

	/**
	 * @param string[] $methods
	 */
	protected function getPush(array $methods = []): Push|MockObject {
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
					$this->random,
					$this->logger,
				])
				->onlyMethods($methods)
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
			$this->timeFactory,
			$this->random,
			$this->logger,
		);
	}

	public function testPushToDeviceNoInternet(): void {
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

		/** @var INotification&MockObject$notification */
		$notification = $this->createMock(INotification::class);

		$push->pushToDevice(23, $notification);
	}

	public function testPushToDeviceNoDevices(): void {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser']);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
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

	public function testPushToDeviceNotPrepared(): void {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser']);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
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

	public function testPushToDeviceInvalidToken(): void {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken']);
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
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


		/** @var Key&MockObject $key */
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

	public function testPushToDeviceEncryptionError(): void {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken', 'validateToken']);
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
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

		/** @var Key&MockObject $key */
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
	public function testPushToDeviceNoFairUse(): void {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken', 'validateToken', 'deletePushTokenByDeviceIdentifier']);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
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

		/** @var Key&MockObject $key */
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

	public static function dataPushToDeviceSending(): array {
		return [
			[true],
			[false],
		];
	}

	/**
	 * @dataProvider dataPushToDeviceSending
	 */
	public function testPushToDeviceSending(bool $isDebug): void {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken', 'validateToken', 'deletePushTokenByDeviceIdentifier']);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
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

		/** @var Key&MockObject $key */
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

		/** @var IClient&MockObject $client */
		$client = $this->createMock(IClient::class);

		$this->clientService->expects($this->once())
			->method('newClient')
			->willReturn($client);

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		// Call 1
		/** @var ResponseInterface&MockObject $response1 */
		$response1 = $this->createMock(ResponseInterface::class);
		$response1->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_BAD_REQUEST);
		/** @var StreamInterface&MockObject $body1 */
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
		/** @var ResponseInterface&MockObject $response1 */
		$response2 = $this->createMock(ResponseInterface::class);
		/** @var StreamInterface&MockObject $body2 */
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
		/** @var IResponse&MockObject $response1 */
		$response3 = $this->createMock(IResponse::class);
		$response3->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_OK);
		/** @var StreamInterface&MockObject $body3 */
		$body3 = $this->createMock(StreamInterface::class);
		$response3->method('getBody')
			->willReturn('');

		// Call 4
		/** @var ResponseInterface&MockObject $response1 */
		$response4 = $this->createMock(ResponseInterface::class);
		$response4->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_BAD_REQUEST);
		/** @var StreamInterface&MockObject $body4 */
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
			->willReturnOnConsecutiveCalls(
				$this->throwException($exception0), // proxyserver1/notifications
				$this->throwException($exception1), // badrequest/notifications
				$this->throwException($exception2), // unavailable/notifications
				$response3, // ok/notifications
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

	public static function dataPushToDeviceTalkNotification(): array {
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
	 */
	public function testPushToDeviceTalkNotification(array $deviceTypes, bool $isTalkNotification, ?int $pushedDevice): void {
		$push = $this->getPush(['createFakeUserObject', 'getDevicesForUser', 'encryptAndSign', 'deletePushToken', 'validateToken']);

		/** @var INotification&MockObject $notification */
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

		/** @var IUser&MockObject $user */
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

		/** @var Key&MockObject $key */
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

			/** @var IClient&MockObject $client */
			$client = $this->createMock(IClient::class);

			$this->clientService->expects($this->once())
				->method('newClient')
				->willReturn($client);

			/** @var IResponse&MockObject $response1 */
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

	public static function dataValidateToken(): array {
		return [
			[1239999999, 1230000000, OCPIToken::WIPE_TOKEN, false],
			[1230000000, 1239999999, OCPIToken::WIPE_TOKEN, false],
			[1230000000, 1239999999, OCPIToken::PERMANENT_TOKEN, true],
			[1239999999, 1230000000, OCPIToken::PERMANENT_TOKEN, true],
			[1230000000, 1230000000, OCPIToken::PERMANENT_TOKEN, false],
		];
	}

	/**
	 * @dataProvider dataValidateToken
	 */
	public function testValidateToken(int $lastCheck, int $lastActivity, int $type, bool $expected): void {
		$token = PublicKeyToken::fromParams([
			'lastCheck' => $lastCheck,
			'lastActivity' => $lastActivity,
			'type' => $type,
		]);

		$this->tokenProvider->method('getTokenById')
			->willReturn($token);

		$push = $this->getPush();
		$this->assertSame($expected, self::invokePrivate($push, 'validateToken', [42, 1234567890]));
	}
}
