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
use OCA\Notifications\Handler;
use OCA\Notifications\Tests\TestCase;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;

class EndpointControllerTest extends TestCase {
	/** @var \OCP\IRequest|\PHPUnit_Framework_MockObject_MockObject */
	protected $request;

	/** @var \OCA\Notifications\Handler|\PHPUnit_Framework_MockObject_MockObject */
	protected $handler;

	/** @var \OCP\Notification\IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $manager;

	/** @var \OCP\IConfig|\PHPUnit_Framework_MockObject_MockObject */
	protected $config;

	/** @var EndpointController */
	protected $controller;

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

		/** @var \OCP\Notification\IManager|\PHPUnit_Framework_MockObject_MockObject */
		$this->manager = $this->getMockBuilder('OCP\Notification\IManager')
			->disableOriginalConstructor()
			->getMock();

		/** @var \OCP\IConfig|\PHPUnit_Framework_MockObject_MockObject */
		$this->config = $this->getMockBuilder('OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function getController(array $methods = []) {
		if (empty($methods)) {
			return new EndpointController(
				'notifications',
				$this->request,
				$this->handler,
				$this->manager,
				$this->config,
				'username'
			);
		} else {
			return $this->getMockBuilder('OCA\Notifications\Controller\EndpointController')
				->setConstructorArgs([
					'notifications',
					$this->request,
					$this->handler,
					$this->manager,
					$this->config,
					'username'
				])
				->setMethods($methods)
				->getMock();
		}
	}

	public function dataGet() {
		return [
			[
				[], [],
			],
			[
				[
					$this->getMockBuilder('OCP\Notification\INotification')
						->disableOriginalConstructor()
						->getMock(),
					$this->getMockBuilder('OCP\Notification\INotification')
						->disableOriginalConstructor()
						->getMock(),
				],
				['$notification', '$notification'],
			],
			[
				[
					$this->getMockBuilder('OCP\Notification\INotification')
						->disableOriginalConstructor()
						->getMock(),
				],
				['$notification'],
			],
		];
	}

	/**
	 * @dataProvider dataGet
	 * @param array $notifications
	 * @param array $expectedData
	 */
	public function testGet(array $notifications, array $expectedData) {
		$controller = $this->getController([
			'notificationToArray',
		]);
		$controller->expects($this->exactly(sizeof($notifications)))
			->method('notificationToArray')
			->willReturn('$notification');

		$filter = $this->getMockBuilder('OCP\Notification\INotification')
			->disableOriginalConstructor()
			->getMock();
		$filter->expects($this->once())
			->method('setUser')
			->willReturn('username');

		$this->manager->expects($this->once())
			->method('createNotification')
			->willReturn($filter);

		$this->handler->expects($this->once())
			->method('get')
			->with($filter)
			->willReturn($notifications);

		$response = $controller->get();
		$this->assertInstanceOf('OCP\AppFramework\Http\JSONResponse', $response);

		$this->assertSame($expectedData, $response->getData());
	}

	public function dataNotificationToArray() {
		return [
			['app1', 'user1', 1234, 'type1', 42, 'subject1', 'message1', 'link1', 'icon1', [], []],
			['app2', 'user2', 1337, 'type2', 21, 'subject2', 'message2', 'link2', 'icon2', [
				$this->getMockBuilder('OCP\Notification\IAction')
					->disableOriginalConstructor()
					->getMock(),
				$this->getMockBuilder('OCP\Notification\IAction')
					->disableOriginalConstructor()
					->getMock(),
			], ['action', 'action']],
		];
	}

	/**
	 * @dataProvider dataNotificationToArray
	 *
	 * @param string $app
	 * @param string $user
	 * @param int $timestamp
	 * @param string $type
	 * @param int $id
	 * @param string $subject
	 * @param string $message
	 * @param string $link
	 * @param string $icon
	 * @param array $actions
	 */
	public function testNotificationToArray($app, $user, $timestamp, $type, $id, $subject, $message, $link, $icon, array $actions, array $actionsExpected) {
		$notification = $this->getMockBuilder('OCP\Notification\INotification')
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
			->willReturn($type);

		$notification->expects($this->once())
			->method('getObjectId')
			->willReturn($id);

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
			'app' => $app,
			'user' => $user,
			'timestamp' => $timestamp,
			'object_type' => $type,
			'object_id' => $id,
			'subject' => $subject,
			'message' => $message,
			'link' => $link,
			'icon' => $icon,
			'actions' => $actionsExpected,
			],
			$this->invokePrivate($controller, 'notificationToArray', [$notification])
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
		$action = $this->getMockBuilder('OCP\Notification\IAction')
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

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function get() {
		$filter = $this->manager->createNotification();
		$filter->setUser($this->user);
		$language = $this->config->getUserValue($this->user, 'core', 'lang', null);

		$notifications = $this->handler->get($filter);

		$data = [];
		foreach ($notifications as $notification) {
			$this->manager->prepare($notification, $language);
			$data[] = $this->notificationToArray($notification);
		}

		return new JSONResponse($data);
	}

	/**
	 * @param INotification $notification
	 * @return array
	 */
	protected function notificationToArray(INotification $notification) {
		$data = [
			'app' => $notification->getApp(),
			'user' => $notification->getUser(),
			'timestamp' => $notification->getTimestamp(),
			'object_type' => $notification->getObjectType(),
			'object_id' => $notification->getObjectId(),
			'subject' => $notification->getParsedSubject(),
			'message' => $notification->getParsedMessage(),
			'link' => $notification->getLink(),
			'icon' => $notification->getIcon(),
			'actions' => [],
		];

		foreach ($notification->getParsedActions() as $action) {
			$data['actions'][] = $this->actionToArray($action);
		}

		return $data;
	}

	/**
	 * @param IAction $action
	 * @return array
	 */
	protected function actionToArray(IAction $action) {
		return [
			'label' => $action->getParsedLabel(),
			'icon' => $action->getIcon(),
			'link' => $action->getLink(),
			'type' => $action->getRequestType(),
		];
	}
}
