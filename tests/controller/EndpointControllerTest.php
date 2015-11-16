<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
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

namespace OCA\Notifications\Tests\Controller;

use OCA\Notifications\Controller\EndpointController;
use OCA\Notifications\Tests\TestCase;
use OCP\AppFramework\Http;

class EndpointControllerTest extends TestCase {
	/** @var \OCP\IRequest|\PHPUnit_Framework_MockObject_MockObject */
	protected $request;

	/** @var \OCA\Notifications\Handler|\PHPUnit_Framework_MockObject_MockObject */
	protected $handler;

	/** @var \OC\Notification\IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $manager;

	/** @var \OCP\IConfig|\PHPUnit_Framework_MockObject_MockObject */
	protected $config;

	/** @var \OCP\IUserSession|\PHPUnit_Framework_MockObject_MockObject */
	protected $session;

	/** @var EndpointController */
	protected $controller;

	/** @var \OCP\IUser|\PHPUnit_Framework_MockObject_MockObject */
	protected $user;

	protected function setUp() {
		parent::setUp();

		/** @var \OCP\IRequest|\PHPUnit_Framework_MockObject_MockObject */
		$this->request = $this->getMockBuilder('OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();

		/** @var \OCA\Notifications\Handler|\PHPUnit_Framework_MockObject_MockObject */
		$this->handler = $this->getMockBuilder('OCA\Notifications\Handler')
			->disableOriginalConstructor()
			->getMock();

		/** @var \OC\Notification\IManager|\PHPUnit_Framework_MockObject_MockObject */
		$this->manager = $this->getMockBuilder('OC\Notification\IManager')
			->disableOriginalConstructor()
			->getMock();

		/** @var \OCP\IConfig|\PHPUnit_Framework_MockObject_MockObject */
		$this->config = $this->getMockBuilder('OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();

		/** @var \OCP\IUserSession|\PHPUnit_Framework_MockObject_MockObject */
		$this->session = $this->getMockBuilder('OCP\IUserSession')
			->disableOriginalConstructor()
			->getMock();

		/** @var \OCP\IUser|\PHPUnit_Framework_MockObject_MockObject */
		$this->user = $this->getMockBuilder('OCP\IUser')
			->disableOriginalConstructor()
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
			return $this->getMockBuilder('OCA\Notifications\Controller\EndpointController')
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
					1 => $this->getMockBuilder('OC\Notification\INotification')
						->disableOriginalConstructor()
						->getMock(),
					3 => $this->getMockBuilder('OC\Notification\INotification')
						->disableOriginalConstructor()
						->getMock(),
				],
				md5(json_encode([1, 3])),
				['$notification', '$notification'],
			],
			[
				[
					42 => $this->getMockBuilder('OC\Notification\INotification')
						->disableOriginalConstructor()
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

		$filter = $this->getMockBuilder('OC\Notification\INotification')
			->disableOriginalConstructor()
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
		$this->assertInstanceOf('OC_OCS_Result', $response);

		$headers = $response->getHeaders();
		$this->assertArrayHasKey('ETag', $headers);
		$this->assertSame($expectedETag, $headers['ETag']);
		$this->assertSame($expectedData, $response->getData());
	}

	public function dataListNotificationsThrows() {
		return [
			[
				[
					1 => $this->getMockBuilder('OC\Notification\INotification')
						->disableOriginalConstructor()
						->getMock(),
					3 => $this->getMockBuilder('OC\Notification\INotification')
						->disableOriginalConstructor()
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

		$filter = $this->getMockBuilder('OC\Notification\INotification')
			->disableOriginalConstructor()
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
		$this->assertInstanceOf('OC_OCS_Result', $response);

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
		$this->assertInstanceOf('OC_OCS_Result', $response);

		$this->assertSame(Http::STATUS_NO_CONTENT, $response->getStatusCode());
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

		$response = $controller->deleteNotification(['id' => (string) $id]);
		$this->assertInstanceOf('OC_OCS_Result', $response);

		$this->assertSame(100, $response->getStatusCode());
	}

	public function testDeleteNotificationNoId() {
		$controller = $this->getController();

		$this->handler->expects($this->never())
			->method('deleteById');

		$response = $controller->deleteNotification([]);
		$this->assertInstanceOf('OC_OCS_Result', $response);

		$this->assertSame(404, $response->getStatusCode());
	}

	public function dataNotificationToArray() {
		return [
			[42, 'app1', 'user1', 1234, 'type1', 42, 'subject1', 'message1', 'link1', 'icon1', [], []],
			[1337, 'app2', 'user2', 1337, 'type2', 21, 'subject2', 'message2', 'link2', 'icon2', [
				$this->getMockBuilder('OC\Notification\IAction')
					->disableOriginalConstructor()
					->getMock(),
				$this->getMockBuilder('OC\Notification\IAction')
					->disableOriginalConstructor()
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
	 * @param string $icon
	 * @param array $actions
	 * @param array $actionsExpected
	 */
	public function testNotificationToArray($id, $app, $user, $timestamp, $objectType, $objectId, $subject, $message, $link, $icon, array $actions, array $actionsExpected) {
		$notification = $this->getMockBuilder('OC\Notification\INotification')
			->disableOriginalConstructor()
			->getMock();

		$notification->expects($this->once())
			->method('getApp')
			->willReturn($app);

		$notification->expects($this->once())
			->method('getUser')
			->willReturn($user);

		$notification->expects($this->once())
			->method('getTimestamp')
			->willReturn($timestamp);

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
			->method('getIcon')
			->willReturn($icon);

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
			'timestamp' => $timestamp,
			'object_type' => $objectType,
			'object_id' => $objectId,
			'subject' => $subject,
			'message' => $message,
			'link' => $link,
			'icon' => $icon,
			'actions' => $actionsExpected,
			],
			$this->invokePrivate($controller, 'notificationToArray', [$id, $notification])
		);
	}

	public function dataActionToArray() {
		return [
			['label1', 'link1', 'GET', 'icon1'],
			['label2', 'link2', 'POST', 'icon2'],
		];
	}

	/**
	 * @dataProvider dataActionToArray
	 *
	 * @param string $label
	 * @param string $link
	 * @param string $requestType
	 * @param string $icon
	 */
	public function testActionToArray($label, $link, $requestType, $icon) {
		$action = $this->getMockBuilder('OC\Notification\IAction')
			->disableOriginalConstructor()
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
			->method('getIcon')
			->willReturn($icon);

		$this->assertEquals([
				'label' => $label,
				'icon' => $icon,
				'link' => $link,
				'type' => $requestType,
			],
			$this->invokePrivate($this->getController(), 'actionToArray', [$action])
		);
	}
}
