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

namespace OCA\Notifications\Tests\Lib;


use OCA\Notifications\Handler;
use OCA\Notifications\Tests\TestCase;

class HandlerTest extends TestCase {
	/** @var \OCA\Notifications\Handler */
	protected $handler;

	protected function setUp() {
		parent::setUp();

		$this->handler = new Handler(
			\OC::$server->getDatabaseConnection()
		);
	}

	public function testFull() {
		$notification = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user',
			'getTimestamp' => time(),
			'getObjectType' => 'notification',
			'getObjectId' => 1337,
			'getSubject' => 'subject',
			'getSubjectParameters' => [],
			'getMessage' => 'message',
			'getMessageParameters' => [],
			'getLink' => 'link',
			'getIcon' => 'icon',
			'getActions' => [
				[
					'getLabel' => 'action_label',
					'getIcon' => 'action_icon',
					'getLink' => 'action_link',
					'getRequestType' => 'GET',
				]
			],
		]);

		// Make sure there is no notification
		$this->assertSame(0, $this->handler->count($notification));

		// Add and count
		$this->handler->add($notification);
		$this->assertSame(1, $this->handler->count($notification));

		// Delete and count again
		$this->handler->delete($notification);
		$this->assertSame(0, $this->handler->count($notification));
	}

	/**
	 * @param array $values
	 * @return \OCP\Notification\INotification|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getNotification(array $values = []) {
		$notification = $this->getMockBuilder('OCP\Notification\INotification')
			->disableOriginalConstructor()
			->getMock();

		foreach ($values as $method => $returnValue) {
			if ($method === 'getActions') {
				$actions = [];
				foreach ($returnValue as $actionData) {
					$action = $this->getMockBuilder('OCP\Notification\IAction')
						->disableOriginalConstructor()
						->getMock();
					foreach ($actionData as $actionMethod => $actionValue) {
						$action->expects($this->any())
							->method($actionMethod)
							->willReturn($actionValue);
					}
					$actions[] = $action;
				}
				$notification->expects($this->any())
					->method($method)
					->willReturn($actions);
			} else {
				$notification->expects($this->any())
					->method($method)
					->willReturn($returnValue);
			}
		}

		return $notification;
	}
}
