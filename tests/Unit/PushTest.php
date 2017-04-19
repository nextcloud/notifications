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


use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\Security\IdentityProof\Key;
use OC\Security\IdentityProof\Manager;
use OCA\Notifications\Push;
use OCP\AppFramework\Http;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IResponse;
use OCP\IConfig;
use OCP\Http\Client\IClientService;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager as INotificationManager;
use OCP\Notification\INotification;

/**
 * Class PushTest
 *
 * @package OCA\Notifications\Tests\Unit
 * @group DB
 */
class PushTest extends TestCase {
	/** @var IDBConnection */
	protected $db;
	/** @var INotificationManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $notificationManager;
	/** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
	protected $config;
	/** @var IProvider|\PHPUnit_Framework_MockObject_MockObject */
	protected $tokenProvider;
	/** @var Manager|\PHPUnit_Framework_MockObject_MockObject */
	protected $keyManager;
	/** @var IUserManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $userManager;
	/** @var IClientService|\PHPUnit_Framework_MockObject_MockObject */
	protected $clientService;
	/** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
	protected $logger;

	protected function setUp() {
		parent::setUp();

		$this->db = \OC::$server->getDatabaseConnection();
		$this->notificationManager = $this->createMock(INotificationManager::class);
		$this->config = $this->createMock(IConfig::class);
		$this->tokenProvider = $this->createMock(IProvider::class);
		$this->keyManager = $this->createMock(Manager::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->clientService = $this->createMock(IClientService::class);
		$this->logger = $this->createMock(ILogger::class);
	}

	/**
	 * @param string[] $methods
	 * @return Push|\PHPUnit_Framework_MockObject_MockObject
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
					$this->userManager,
					$this->clientService,
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
			$this->userManager,
			$this->clientService,
			$this->logger
		);
	}

	public function testPushToDeviceInvalidUser() {
		$push = $this->getPush();
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		/** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification->expects($this->once())
			->method('getUser')
			->willReturn('invalid');

		$this->userManager->expects($this->once())
			->method('get')
			->with('invalid')
			->willReturn(null);

		$push->pushToDevice($notification);
	}

	public function testPushToDeviceNoDevices() {
		$push = $this->getPush(['getDevicesForUser']);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		/** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification->expects($this->exactly(2))
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|\PHPUnit_Framework_MockObject_MockObject $user */
		$user = $this->createMock(IUser::class);

		$this->userManager->expects($this->once())
			->method('get')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([]);

		$push->pushToDevice($notification);
	}

	public function testPushToDeviceNotPrepared() {
		$push = $this->getPush(['getDevicesForUser']);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		/** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification->expects($this->exactly(3))
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|\PHPUnit_Framework_MockObject_MockObject $user */
		$user = $this->createMock(IUser::class);

		$this->userManager->expects($this->once())
			->method('get')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 'token1',
			]]);

		$this->config->expects($this->once())
			->method('getUserValue')
			->with('valid', 'core', 'lang', 'en')
			->willReturn('de');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'de')
			->willThrowException(new \InvalidArgumentException());

		$push->pushToDevice($notification);
	}

	public function testPushToDeviceInvalidToken() {
		$push = $this->getPush(['getDevicesForUser', 'encryptAndSign', 'deletePushToken']);
		$this->clientService->expects($this->never())
			->method('newClient');

		/** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification->expects($this->exactly(3))
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|\PHPUnit_Framework_MockObject_MockObject $user */
		$user = $this->createMock(IUser::class);

		$this->userManager->expects($this->once())
			->method('get')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 23,
			]]);

		$this->config->expects($this->once())
			->method('getUserValue')
			->with('valid', 'core', 'lang', 'en')
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);


		/** @var Key|\PHPUnit_Framework_MockObject_MockObject $key */
		$key = $this->createMock(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->once())
			->method('encryptAndSign')
			->willThrowException(new InvalidTokenException());

		$push->expects($this->once())
			->method('deletePushToken')
			->with(23);

		$push->pushToDevice($notification);
	}

	public function testPushToDeviceEncryptionError() {
		$push = $this->getPush(['getDevicesForUser', 'encryptAndSign', 'deletePushToken']);
		$this->clientService->expects($this->never())
			->method('newClient');

		/** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification->expects($this->exactly(3))
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|\PHPUnit_Framework_MockObject_MockObject $user */
		$user = $this->createMock(IUser::class);

		$this->userManager->expects($this->once())
			->method('get')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 23,
			]]);

		$this->config->expects($this->once())
			->method('getUserValue')
			->with('valid', 'core', 'lang', 'en')
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);


		/** @var Key|\PHPUnit_Framework_MockObject_MockObject $key */
		$key = $this->createMock(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->once())
			->method('encryptAndSign')
			->willThrowException(new \InvalidArgumentException());

		$push->expects($this->once())
			->method('deletePushToken')
			->with(23);

		$push->pushToDevice($notification);
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
		$push = $this->getPush(['getDevicesForUser', 'encryptAndSign', 'deletePushToken']);

		/** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification->expects($this->exactly(3))
			->method('getUser')
			->willReturn('valid');

		/** @var IUser|\PHPUnit_Framework_MockObject_MockObject $user */
		$user = $this->createMock(IUser::class);

		$this->userManager->expects($this->once())
			->method('get')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getDevicesForUser')
			->willReturn([
				[
					'proxyserver' => 'proxyserver1',
					'token' => 16,
				],
				[
					'proxyserver' => 'proxyserver1/',
					'token' => 23,
				],
				[
					'proxyserver' => 'badrequest',
					'token' => 42,
				],
				[
					'proxyserver' => 'unavailable',
					'token' => 48,
				],
				[
					'proxyserver' => 'ok',
					'token' => 64,
				],
			]);

		$this->config->expects($this->once())
			->method('getUserValue')
			->with('valid', 'core', 'lang', 'en')
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key|\PHPUnit_Framework_MockObject_MockObject $key */
		$key = $this->createMock(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->exactly(5))
			->method('encryptAndSign')
			->willReturn(['Payload']);

		$push->expects($this->never())
			->method('deletePushToken');

		/** @var IClient|\PHPUnit_Framework_MockObject_MockObject $client */
		$client = $this->createMock(IClient::class);

		$this->clientService->expects($this->once())
			->method('newClient')
			->willReturn($client);

		$e = new \Exception();
		$client->expects($this->at(0))
			->method('post')
			->with('proxyserver1/notifications', [
					'body' => [
						'notifications' => ['["Payload"]', '["Payload"]'],
					],
				])
			->willThrowException($e);

		$this->logger->expects($this->at(0))
			->method('logException')
			->with($e, [
				'app' => 'notifications',
			]);

		/** @var IResponse|\PHPUnit_Framework_MockObject_MockObject $response1 */
		$response1 = $this->createMock(IResponse::class);
		$response1->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_BAD_REQUEST);
		$response1->expects($this->once())
			->method('getBody')
			->willReturn(null);
		$client->expects($this->at(1))
			->method('post')
			->with('badrequest/notifications', [
					'body' => [
						'notifications' => ['["Payload"]'],
					],
				])
			->willReturn($response1);

		$this->logger->expects($this->at(1))
			->method('error')
			->with('Could not send notification to push server [{url}]: {error}', [
				'error' => 'no reason given',
				'url' => 'badrequest',
				'app' => 'notifications',
			]);

		/** @var IResponse|\PHPUnit_Framework_MockObject_MockObject $response1 */
		$response2 = $this->createMock(IResponse::class);
		$response2->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_SERVICE_UNAVAILABLE);
		$response2->expects($isDebug ? $this->once() : $this->never())
			->method('getBody')
			->willReturn('Maintenance');
		$client->expects($this->at(2))
			->method('post')
			->with('unavailable/notifications', [
					'body' => [
						'notifications' => ['["Payload"]'],
					],
				])
			->willReturn($response2);

		$this->config->expects($this->once())
			->method('getSystemValue')
			->with('debug', false)
			->willReturn($isDebug);

		$this->logger->expects($isDebug ? $this->at(2) : $this->never())
			->method('debug')
			->with('Could not send notification to push server [{url}]: {error}', [
				'error' => 'Maintenance',
				'url' => 'unavailable',
				'app' => 'notifications',
			]);

		/** @var IResponse|\PHPUnit_Framework_MockObject_MockObject $response1 */
		$response3 = $this->createMock(IResponse::class);
		$response3->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_OK);
		$client->expects($this->at(3))
			->method('post')
			->with('ok/notifications', [
					'body' => [
						'notifications' => ['["Payload"]'],
					],
				])
			->willReturn($response3);

		$push->pushToDevice($notification);
	}
}
