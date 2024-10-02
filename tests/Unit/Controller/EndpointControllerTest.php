<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications\Tests\Unit\Controller;

use OCA\Notifications\Controller\EndpointController;
use OCA\Notifications\Exceptions\NotificationNotFoundException;
use OCA\Notifications\Handler;
use OCA\Notifications\Push;
use OCA\Notifications\Service\ClientService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\UserStatus\IManager as IUserStatusManager;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class EndpointControllerTest extends TestCase {
	protected IRequest&MockObject $request;
	protected Handler&MockObject $handler;
	protected IManager&MockObject $manager;
	protected IFactory&MockObject $l10nFactory;
	protected IUserSession&MockObject $session;
	protected IUserStatusManager&MockObject $userStatusManager;
	protected IUser&MockObject $user;
	protected ITimeFactory&MockObject $timeFactory;
	protected ClientService&MockObject $clientService;
	protected Push&MockObject $push;
	protected EndpointController$controller;


	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->handler = $this->createMock(Handler::class);
		$this->manager = $this->createMock(IManager::class);
		$this->l10nFactory = $this->createMock(IFactory::class);
		$this->session = $this->createMock(IUserSession::class);
		$this->userStatusManager = $this->createMock(IUserStatusManager::class);
		$this->user = $this->createMock(IUser::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->clientService = $this->createMock(ClientService::class);
		$this->push = $this->createMock(Push::class);

		$this->session->expects($this->any())
			->method('getUser')
			->willReturn($this->user);
	}

	protected function getController(array $methods = [], $username = 'username') {
		$this->user->expects($this->any())
			->method('getUID')
			->willReturn($username);

		if (empty($methods)) {
			return new EndpointController(
				'notifications',
				$this->request,
				$this->handler,
				$this->manager,
				$this->l10nFactory,
				$this->session,
				$this->timeFactory,
				$this->userStatusManager,
				$this->clientService,
				$this->push,
			);
		}

		return $this->getMockBuilder(EndpointController::class)
			->setConstructorArgs([
				'notifications',
				$this->request,
				$this->handler,
				$this->manager,
				$this->l10nFactory,
				$this->session,
				$this->timeFactory,
				$this->userStatusManager,
				$this->clientService,
				$this->push,
			])
			->setMethods($methods)
			->getMock();
	}

	public function dataListNotifications(): array {
		return [
			[
				'v2',
				[],
				'"' . md5(json_encode([])) . '"',
				[],
			],
			[
				'v2',
				[
					1 => $this->getMockBuilder(INotification::class)
						->getMock(),
					3 => $this->getMockBuilder(INotification::class)
						->getMock(),
				],
				'"' . md5(json_encode([1, 3])) . '"',
				[['$notification'], ['$notification']],
			],
			[
				'v2',
				[
					42 => $this->getMockBuilder(INotification::class)
						->getMock(),
				],
				'"' . md5(json_encode([42])) . '"',
				[['$notification']],
			],
		];
	}

	/**
	 * @dataProvider dataListNotifications
	 * @param string $apiVersion
	 * @param array $notifications
	 * @param string $expectedETag
	 * @param array $expectedData
	 */
	public function testListNotifications(string $apiVersion, array $notifications, string $expectedETag, array $expectedData): void {
		$controller = $this->getController([
			'notificationToArray',
		]);
		$controller->expects($this->exactly(\count($notifications)))
			->method('notificationToArray')
			->willReturn(['$notification']);

		$filter = $this->getMockBuilder(INotification::class)
			->getMock();
		$filter->expects($this->once())
			->method('setUser')
			->willReturn($filter);

		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn(true);
		$this->manager->expects($this->once())
			->method('createNotification')
			->willReturn($filter);
		$this->manager->expects($this->exactly(\count($notifications)))
			->method('prepare')
			->willReturnArgument(0);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($this->user)
			->willReturn('en');

		$this->handler->expects($this->once())
			->method('get')
			->with($filter)
			->willReturn($notifications);

		$response = $controller->listNotifications($apiVersion);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());

		$headers = $response->getHeaders();
		$this->assertArrayHasKey('ETag', $headers);
		$this->assertSame($expectedETag, $headers['ETag']);
		$this->assertSame($expectedData, $response->getData());
	}

	public function dataListNotificationsThrows() {
		return [
			[
				'v2',
				[
					1 => $this->getMockBuilder(INotification::class)
						->getMock(),
					3 => $this->getMockBuilder(INotification::class)
						->getMock(),
				],
				'"' . md5(json_encode([3])) . '"',
				[['$notification']],
			],
		];
	}

	/**
	 * @dataProvider dataListNotificationsThrows
	 * @param string $apiVersion
	 * @param array $notifications
	 * @param string $expectedETag
	 * @param array $expectedData
	 */
	public function testListNotificationsThrows($apiVersion, array $notifications, $expectedETag, array $expectedData) {
		$controller = $this->getController([
			'notificationToArray',
		]);
		$controller->expects($this->exactly(1))
			->method('notificationToArray')
			->willReturn(['$notification']);

		$filter = $this->getMockBuilder(INotification::class)
			->getMock();
		$filter->expects($this->once())
			->method('setUser')
			->willReturn($filter);

		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn(true);
		$this->manager->expects($this->once())
			->method('createNotification')
			->willReturn($filter);
		$this->manager->expects($this->once())
			->method('defer')
			->willReturn(true);
		$this->manager->expects($this->once())
			->method('flush');

		$throw = true;
		$this->manager->expects($this->exactly(2))
			->method('prepare')
			->willReturnCallback(function ($arg) use (&$throw) {
				if ($throw) {
					$throw = false;
					throw new \InvalidArgumentException();
				}
				return $arg;
			});

		$this->l10nFactory
			->method('getUserLanguage')
			->with($this->user)
			->willReturn('en');

		$this->handler->expects($this->once())
			->method('get')
			->with($filter)
			->willReturn($notifications);

		$response = $controller->listNotifications($apiVersion);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());

		$headers = $response->getHeaders();
		$this->assertArrayHasKey('ETag', $headers);
		$this->assertSame($expectedETag, $headers['ETag']);
		$this->assertSame($expectedData, $response->getData());
	}

	public function dataListNotificationsNoNotifiers() {
		return [
			['v1'],
			['v2'],
		];
	}

	/**
	 * @dataProvider dataListNotificationsNoNotifiers
	 * @param string $apiVersion
	 */
	public function testListNotificationsNoNotifiers($apiVersion) {
		$controller = $this->getController();
		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn(false);

		$response = $controller->listNotifications($apiVersion);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_NO_CONTENT, $response->getStatus());
	}

	public function dataGetNotification() {
		return [
			['v1', 42, 'username1', [['$notification']]],
			['v2', 21, 'username2', [['$notification']]],
		];
	}

	/**
	 * @dataProvider dataGetNotification
	 * @param string $apiVersion
	 * @param int $id
	 * @param string $username
	 */
	public function testGetNotification($apiVersion, $id, $username) {
		$controller = $this->getController([
			'notificationToArray',
		], $username);

		$notification = $this->getMockBuilder(INotification::class)
			->getMock();

		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn(true);
		$this->manager->expects($this->once())
			->method('prepare')
			->with($notification)
			->willReturn($notification);

		$this->handler->expects($this->once())
			->method('getById')
			->with($id, $username)
			->willReturn($notification);

		$controller->expects($this->exactly(1))
			->method('notificationToArray')
			->with($id, $notification)
			->willReturn(['$notification']);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($this->user)
			->willReturn('en');

		$response = $controller->getNotification($apiVersion, $id);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function dataGetNotificationNoId(): array {
		$notification = $this->getMockBuilder(INotification::class)
			->getMock();

		return [
			['v1', false, 42, false, new NotificationNotFoundException()], // No notifiers
			['v1', true, 42, true, new NotificationNotFoundException()], // Not found in database
			['v1', true, 42, true, $notification], // Not handled on prepare
			['v2', true, 42, true, $notification], // Not handled on prepare
		];
	}

	/**
	 * @dataProvider dataGetNotificationNoId
	 */
	public function testGetNotificationNoId(string $apiVersion, bool $hasNotifiers, int $id, bool $called, NotificationNotFoundException|INotification $notification): void {
		$controller = $this->getController();

		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn($hasNotifiers);

		if ($notification instanceof NotificationNotFoundException) {
			$this->handler->expects($called ? $this->once() : $this->never())
				->method('getById')
				->willThrowException($notification);

			$this->manager->expects($called && !$notification instanceof NotificationNotFoundException ? $this->once() : $this->never())
				->method('prepare')
				->willThrowException(new \InvalidArgumentException());
		} else {
			$this->handler->expects($this->once())
				->method('getById')
				->willReturn($notification);

			$this->l10nFactory
				->method('getUserLanguage')
				->with($this->user)
				->willReturn('en');

			$this->manager->expects($this->once())
				->method('prepare')
				->willThrowException(new \InvalidArgumentException());
		}

		$response = $controller->getNotification($apiVersion, $id);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	public function dataDeleteNotification() {
		return [
			[42, 'username1'],
			[21, 'username2'],
		];
	}

	/**
	 * @dataProvider dataDeleteNotification
	 * @param int $id
	 * @param string $username
	 */
	public function testDeleteNotification($id, $username) {
		$controller = $this->getController([], $username);

		$this->handler->expects($this->once())
			->method('deleteById')
			->with($id, $username);

		$response = $controller->deleteNotification($id);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function testDeleteNotificationNoId() {
		$controller = $this->getController();

		$this->handler->expects($this->never())
			->method('deleteById');

		$response = $controller->deleteNotification(0);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	/**
	 * @dataProvider dataDeleteNotification
	 * @param int $_
	 * @param string $username
	 */
	public function testDeleteAllNotifications($_, $username) {
		$controller = $this->getController([], $username);

		$this->handler->expects($this->once())
			->method('deleteByUser')
			->with($username);
		$this->manager->expects($this->once())
			->method('defer')
			->willReturn(true);
		$this->manager->expects($this->once())
			->method('flush');

		$response = $controller->deleteAllNotifications();
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function dataNotificationToArray() {
		return [
			['v1', 42, 'app1', 'user1', 1234, 'type1', '42', 'subject1', '', [], 'message1', 'richMessage 1', ['richMessage param'], 'link1', 'icon1', [], []],
			['v1', 1337, 'app2', 'user2', 1337, 'type2', '21', 'subject2', 'richSubject 2', ['richSubject param'], 'message2', '', [], 'link2', 'icon2', [
				$this->getMockBuilder(IAction::class)
					->getMock(),
				$this->getMockBuilder(IAction::class)
					->getMock(),
			], [['action'], ['action']]],
			['v2', 42, 'app1', 'user1', 1234, 'type1', '42', 'subject1', '', [], 'message1', 'richMessage 1', ['richMessage param'], 'link1', 'icon1', [], []],
			['v2', 1337, 'app2', 'user2', 1337, 'type2', '21', 'subject2', 'richSubject 2', ['richSubject param'], 'message2', '', [], 'link2', 'icon2', [
				$this->getMockBuilder(IAction::class)
					->getMock(),
				$this->getMockBuilder(IAction::class)
					->getMock(),
			], [['action'], ['action']]],
		];
	}

	/**
	 * @dataProvider dataNotificationToArray
	 *
	 * @param string $apiVersion
	 * @param int $id
	 * @param string $app
	 * @param string $user
	 * @param int $timestamp
	 * @param string $objectType
	 * @param int $objectId
	 * @param string $subject
	 * @param string $subjectRich
	 * @param array $subjectRichParameters
	 * @param string $message
	 * @param string $messageRich
	 * @param array $messageRichParameters
	 * @param string $link
	 * @param string $icon
	 * @param array $actions
	 * @param array $actionsExpected
	 */
	public function testNotificationToArray($apiVersion, $id, $app, $user, $timestamp, $objectType, $objectId, $subject, $subjectRich, $subjectRichParameters, $message, $messageRich, $messageRichParameters, $link, $icon, array $actions, array $actionsExpected) {
		$notification = $this->getMockBuilder(INotification::class)
			->getMock();

		$notification->expects($this->once())
			->method('getApp')
			->willReturn($app);

		$notification->expects($this->once())
			->method('getUser')
			->willReturn($user);

		$dateTime = new \DateTime();
		$dateTime->setTimestamp($timestamp);
		$notification->expects($this->once())
			->method('getDateTime')
			->willReturn($dateTime);

		$notification->expects($this->once())
			->method('getObjectType')
			->willReturn($objectType);

		$notification->expects($this->once())
			->method('getObjectId')
			->willReturn($objectId);

		$notification->expects($this->once())
			->method('getParsedSubject')
			->willReturn($subject);

		$notification->expects($apiVersion === 'v1' ? $this->never() : $this->once())
			->method('getRichSubject')
			->willReturn($subjectRich);

		$notification->expects($apiVersion === 'v1' ? $this->never() : $this->once())
			->method('getRichSubjectParameters')
			->willReturn($subjectRichParameters);

		$notification->expects($this->once())
			->method('getParsedMessage')
			->willReturn($message);

		$notification->expects($apiVersion === 'v1' ? $this->never() : $this->once())
			->method('getRichMessage')
			->willReturn($messageRich);

		$notification->expects($apiVersion === 'v1' ? $this->never() : $this->once())
			->method('getRichMessageParameters')
			->willReturn($messageRichParameters);

		$notification->expects($this->once())
			->method('getLink')
			->willReturn($link);

		$notification->expects($apiVersion === 'v1' ? $this->never() : $this->once())
			->method('getIcon')
			->willReturn($icon);

		$notification->expects($this->once())
			->method('getParsedActions')
			->willReturn($actions);

		$controller = $this->getController([
			'actionToArray'
		]);
		$controller->expects($this->exactly(\count($actions)))
			->method('actionToArray')
			->willReturn(['action']);

		$expected = [
			'notification_id' => $id,
			'app' => $app,
			'user' => $user,
			'datetime' => date('c', $timestamp),
			'object_type' => $objectType,
			'object_id' => $objectId,
			'subject' => $subject,
			'message' => $message,
			'link' => $link,
			'actions' => $actionsExpected,
		];

		if ($apiVersion !== 'v1') {
			$expected = array_merge($expected, [
				'subjectRich' => $subjectRich,
				'subjectRichParameters' => $subjectRichParameters,
				'messageRich' => $messageRich,
				'messageRichParameters' => $messageRichParameters,
				'icon' => $icon,
				'shouldNotify' => true,
			]);
		}

		$this->assertEquals($expected, $this->invokePrivate($controller, 'notificationToArray', [$id, $notification, $apiVersion])
		);
	}

	public function dataActionToArray() {
		return [
			['label1', 'link1', 'GET', false],
			['label2', 'link2', 'POST', true],
		];
	}

	/**
	 * @dataProvider dataActionToArray
	 *
	 * @param string $label
	 * @param string $link
	 * @param string $requestType
	 * @param bool $isPrimary
	 */
	public function testActionToArray($label, $link, $requestType, $isPrimary) {
		$action = $this->getMockBuilder(IAction::class)
			->getMock();

		$action->expects($this->once())
			->method('getParsedLabel')
			->willReturn($label);

		$action->expects($this->once())
			->method('getLink')
			->willReturn($link);

		$action->expects($this->once())
			->method('getRequestType')
			->willReturn($requestType);

		$action->expects($this->once())
			->method('isPrimary')
			->willReturn($isPrimary);

		$this->assertEquals([
			'label' => $label,
			'link' => $link,
			'type' => $requestType,
			'primary' => $isPrimary,
		],
			$this->invokePrivate($this->getController(), 'actionToArray', [$action])
		);
	}
}
