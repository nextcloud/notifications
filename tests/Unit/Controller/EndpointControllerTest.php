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

namespace OCA\Notifications\Tests\Unit\Controller;

use OCA\Notifications\Controller\EndpointController;
use OCA\Notifications\Handler;
use OCA\Notifications\Tests\Unit\TestCase;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;

class EndpointControllerTest extends TestCase {
	/** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
	protected $request;

	/** @var Handler|\PHPUnit_Framework_MockObject_MockObject */
	protected $handler;

	/** @var IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $manager;

	/** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
	protected $config;

	/** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
	protected $session;

	/** @var EndpointController */
	protected $controller;

	/** @var IUser|\PHPUnit_Framework_MockObject_MockObject */
	protected $user;

	protected function setUp() {
		parent::setUp();

		/** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
		$this->request = $this->getMockBuilder(IRequest::class)
			->getMock();

		/** @var Handler|\PHPUnit_Framework_MockObject_MockObject */
		$this->handler = $this->getMockBuilder(Handler::class)
			->disableOriginalConstructor()
			->getMock();

		/** @var IManager|\PHPUnit_Framework_MockObject_MockObject */
		$this->manager = $this->getMockBuilder(IManager::class)
			->getMock();

		/** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
		$this->config = $this->getMockBuilder(IConfig::class)
			->getMock();

		/** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
		$this->session = $this->getMockBuilder(IUserSession::class)
			->getMock();

		/** @var IUser|\PHPUnit_Framework_MockObject_MockObject */
		$this->user = $this->getMockBuilder(IUser::class)
			->getMock();

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
				$this->config,
				$this->session
			);
		} else {
			return $this->getMockBuilder(EndpointController::class)
				->setConstructorArgs([
					'notifications',
					$this->request,
					$this->handler,
					$this->manager,
					$this->config,
					$this->session
				])
				->setMethods($methods)
				->getMock();
		}
	}

	public function dataListNotifications() {
		return [
			[
				[], md5(json_encode([])), [],
			],
			[
				[
					1 => $this->getMockBuilder(INotification::class)
						->getMock(),
					3 => $this->getMockBuilder(INotification::class)
						->getMock(),
				],
				md5(json_encode([1, 3])),
				['$notification', '$notification'],
			],
			[
				[
					42 => $this->getMockBuilder(INotification::class)
						->getMock(),
				],
				md5(json_encode([42])),
				['$notification'],
			],
		];
	}

	/**
	 * @dataProvider dataListNotifications
	 * @param array $notifications
	 * @param string $expectedETag
	 * @param array $expectedData
	 */
	public function testListNotifications(array $notifications, $expectedETag, array $expectedData) {
		$controller = $this->getController([
			'notificationToArray',
		]);
		$controller->expects($this->exactly(sizeof($notifications)))
			->method('notificationToArray')
			->willReturn('$notification');

		$filter = $this->getMockBuilder(INotification::class)
			->getMock();
		$filter->expects($this->once())
			->method('setUser')
			->willReturn('username');

		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn(true);
		$this->manager->expects($this->once())
			->method('createNotification')
			->willReturn($filter);
		$this->manager->expects($this->exactly(sizeof($notifications)))
			->method('prepare')
			->willReturnArgument(0);

		$this->handler->expects($this->once())
			->method('get')
			->with($filter)
			->willReturn($notifications);

		$response = $controller->listNotifications();
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
				[
					1 => $this->getMockBuilder(INotification::class)
						->getMock(),
					3 => $this->getMockBuilder(INotification::class)
						->getMock(),
				],
				md5(json_encode([3])),
				['$notification'],
			],
		];
	}

	/**
	 * @dataProvider dataListNotificationsThrows
	 * @param array $notifications
	 * @param string $expectedETag
	 * @param array $expectedData
	 */
	public function testListNotificationsThrows(array $notifications, $expectedETag, array $expectedData) {
		$controller = $this->getController([
			'notificationToArray',
		]);
		$controller->expects($this->exactly(1))
			->method('notificationToArray')
			->willReturn('$notification');

		$filter = $this->getMockBuilder(INotification::class)
			->getMock();
		$filter->expects($this->once())
			->method('setUser')
			->willReturn('username');

		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn(true);
		$this->manager->expects($this->once())
			->method('createNotification')
			->willReturn($filter);
		$this->manager->expects($this->at(2))
			->method('prepare')
			->willThrowException(new \InvalidArgumentException());
		$this->manager->expects($this->at(3))
			->method('prepare')
			->willReturnArgument(0);

		$this->handler->expects($this->once())
			->method('get')
			->with($filter)
			->willReturn($notifications);

		$response = $controller->listNotifications();
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());

		$headers = $response->getHeaders();
		$this->assertArrayHasKey('ETag', $headers);
		$this->assertSame($expectedETag, $headers['ETag']);
		$this->assertSame($expectedData, $response->getData());
	}

	public function testListNotificationsNoNotifiers() {
		$controller = $this->getController();
		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn(false);

		$response = $controller->listNotifications();
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_NO_CONTENT, $response->getStatus());
	}

	public function dataGetNotification() {
		return [
			[42, 'username1', ['$notification']],
			[21, 'username2', ['$notification']],
		];
	}

	/**
	 * @dataProvider dataGetNotification
	 * @param int $id
	 * @param string $username
	 */
	public function testGetNotification($id, $username) {
		$controller = $this->getController([
			'notificationToArray',
		], $username);

		$notification = $this->getMockBuilder(INotification::class)
			->getMock();

		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn(true);
		$this->manager->expects($this->once() )
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
			->willReturn('$notification');

		$response = $controller->getNotification($id);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function dataGetNotificationNoId() {
		$notification = $this->getMockBuilder(INotification::class)
			->getMock();

		return [
			[false, null, false, null], // No notifiers
			[true, null, false, null], // No id
			[true, 42, true, null], // Not found in database
			[true, 42, true, $notification], // Not handled on prepare
		];
	}

	/**
	 * @dataProvider dataGetNotificationNoId
	 * @param bool $hasNotifiers
	 * @param mixed $id
	 * @param bool $called
	 * @param null|INotification $notification
	 */
	public function testGetNotificationNoId($hasNotifiers, $id, $called, $notification) {
		$controller = $this->getController();

		$this->manager->expects($this->once())
			->method('hasNotifiers')
			->willReturn($hasNotifiers);

		$this->handler->expects($called ? $this->once() : $this->never())
			->method('getById')
			->willReturn($notification);

		$this->manager->expects($called && $notification ? $this->once() : $this->never())
			->method('prepare')
			->willThrowException(new \InvalidArgumentException());

		$response = $controller->getNotification($id);
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

		$response = $controller->deleteNotification([]);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	public function dataNotificationToArray() {
		return [
			[42, 'app1', 'user1', 1234, 'type1', 42, 'subject1', 'message1', 'link1', [], []],
			[1337, 'app2', 'user2', 1337, 'type2', 21, 'subject2', 'message2', 'link2', [
				$this->getMockBuilder(IAction::class)
					->getMock(),
				$this->getMockBuilder(IAction::class)
					->getMock(),
			], ['action', 'action']],
		];
	}

	/**
	 * @dataProvider dataNotificationToArray
	 *
	 * @param int $id
	 * @param string $app
	 * @param string $user
	 * @param int $timestamp
	 * @param string $objectType
	 * @param int $objectId
	 * @param string $subject
	 * @param string $message
	 * @param string $link
	 * @param array $actions
	 * @param array $actionsExpected
	 */
	public function testNotificationToArray($id, $app, $user, $timestamp, $objectType, $objectId, $subject, $message, $link, array $actions, array $actionsExpected) {
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

		$notification->expects($this->once())
			->method('getParsedMessage')
			->willReturn($message);

		$notification->expects($this->once())
			->method('getLink')
			->willReturn($link);

		$notification->expects($this->once())
			->method('getParsedActions')
			->willReturn($actions);

		$controller = $this->getController([
			'actionToArray'
		]);
		$controller->expects($this->exactly(sizeof($actions)))
			->method('actionToArray')
			->willReturn('action');

		$this->assertEquals([
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
			],
			$this->invokePrivate($controller, 'notificationToArray', [$id, $notification])
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
