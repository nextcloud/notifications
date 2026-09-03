<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications\Tests\Unit;

use OCA\Notifications\Exceptions\NotificationNotFoundException;
use OCA\Notifications\Handler;
use OCP\IDBConnection;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

#[Group(name: 'DB')]
class HandlerTest extends TestCase {
	/** @var Handler */
	protected $handler;

	protected function setUp(): void {
		parent::setUp();

		$this->handler = new Handler(
			\OCP\Server::get(IDBConnection::class),
			\OCP\Server::get(IManager::class),
		);

		$this->handler->delete($this->getNotification([
			'getApp' => 'testing_notifications',
		]));
	}

	protected function tearDown(): void {
		$this->handler = new Handler(
			\OCP\Server::get(IDBConnection::class),
			\OCP\Server::get(IManager::class),
		);

		$this->handler->delete($this->getNotification([
			'getApp' => 'testing_notifications',
		]));
		parent::tearDown();
	}

	public function testFull(): void {
		$notification = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user1',
			'getDateTime' => new \DateTime(),
			'getObjectType' => 'notification',
			'getObjectId' => '1337',
			'getSubject' => 'subject',
			'getSubjectParameters' => [],
			'getMessage' => 'message',
			'getMessageParameters' => [],
			'getLink' => 'https://example.tld/notification',
			'getIcon' => 'https://example.tld/icon',
			'getActions' => [
				[
					'getLabel' => 'action_label',
					'getLink' => 'https://example.tld/action',
					'getRequestType' => 'GET',
					'isPrimary' => false,
				]
			],
		]);
		$limitedNotification1 = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user1',
		]);
		$limitedNotification2 = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user2',
		]);

		// Make sure there is no notification
		$this->assertSame(0, $this->handler->count($limitedNotification1), 'Wrong notification count for user1 before adding');
		$notifications = $this->handler->get($limitedNotification1);
		$this->assertCount(0, $notifications, 'Wrong notification count for user1 before beginning');
		$this->assertSame(0, $this->handler->count($limitedNotification2), 'Wrong notification count for user2 before adding');
		$notifications = $this->handler->get($limitedNotification2);
		$this->assertCount(0, $notifications, 'Wrong notification count for user2 before beginning');

		// Add and count
		$this->assertGreaterThan(0, $this->handler->add($notification));
		$this->assertSame(1, $this->handler->count($limitedNotification1), 'Wrong notification count for user1 after adding');
		$this->assertSame(0, $this->handler->count($limitedNotification2), 'Wrong notification count for user2 after adding');

		// Get and count
		$notifications = $this->handler->get($limitedNotification1);
		$this->assertCount(1, $notifications, 'Wrong notification get for user1 after adding');
		$notifications = $this->handler->get($limitedNotification2);
		$this->assertCount(0, $notifications, 'Wrong notification get for user2 after adding');

		// Delete and count again
		$this->handler->delete($notification);
		$this->assertSame(0, $this->handler->count($limitedNotification1), 'Wrong notification count for user1 after deleting');
		$this->assertSame(0, $this->handler->count($limitedNotification2), 'Wrong notification count for user2 after deleting');
	}

	public function testFullEmptyMessageForOracle(): void {
		$notification = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user1',
			'getDateTime' => new \DateTime(),
			'getObjectType' => 'notification',
			'getObjectId' => '1337',
			'getSubject' => 'subject',
			'getSubjectParameters' => [],
			'getMessage' => '',
			'getMessageParameters' => [],
			'getLink' => 'https://example.tld/notification',
			'getIcon' => 'https://example.tld/icon',
			'getActions' => [
				[
					'getLabel' => 'action_label',
					'getLink' => 'https://example.tld/action',
					'getRequestType' => 'GET',
					'isPrimary' => false,
				]
			],
		]);
		$limitedNotification1 = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user1',
		]);
		$limitedNotification2 = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user2',
		]);

		// Make sure there is no notification
		$this->assertSame(0, $this->handler->count($limitedNotification1), 'Wrong notification count for user1 before adding');
		$notifications = $this->handler->get($limitedNotification1);
		$this->assertCount(0, $notifications, 'Wrong notification count for user1 before beginning');
		$this->assertSame(0, $this->handler->count($limitedNotification2), 'Wrong notification count for user2 before adding');
		$notifications = $this->handler->get($limitedNotification2);
		$this->assertCount(0, $notifications, 'Wrong notification count for user2 before beginning');

		// Add and count
		$this->assertGreaterThan(0, $this->handler->add($notification));
		$this->assertSame(1, $this->handler->count($limitedNotification1), 'Wrong notification count for user1 after adding');
		$this->assertSame(0, $this->handler->count($limitedNotification2), 'Wrong notification count for user2 after adding');

		// Get and count
		$notifications = $this->handler->get($limitedNotification1);
		$this->assertCount(1, $notifications, 'Wrong notification get for user1 after adding');
		$notifications = $this->handler->get($limitedNotification2);
		$this->assertCount(0, $notifications, 'Wrong notification get for user2 after adding');

		// Delete and count again
		$this->handler->delete($notification);
		$this->assertSame(0, $this->handler->count($limitedNotification1), 'Wrong notification count for user1 after deleting');
		$this->assertSame(0, $this->handler->count($limitedNotification2), 'Wrong notification count for user2 after deleting');
	}

	public function testDeleteById(): void {
		$notification = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user1',
			'getDateTime' => new \DateTime(),
			'getObjectType' => 'notification',
			'getObjectId' => '1337',
			'getSubject' => 'subject',
			'getSubjectParameters' => [],
			'getMessage' => 'message',
			'getMessageParameters' => [],
			'getLink' => 'https://example.tld/notification',
			'getIcon' => 'https://example.tld/icon',
			'getActions' => [
				[
					'getLabel' => 'action_label',
					'getLink' => 'https://example.tld/action',
					'getRequestType' => 'GET',
					'isPrimary' => true,
				]
			],
		]);
		$limitedNotification = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => 'test_user1',
		]);

		// Make sure there is no notification
		$this->assertSame(0, $this->handler->count($limitedNotification));
		$notifications = $this->handler->get($limitedNotification);
		$this->assertCount(0, $notifications);

		// Add and count
		$this->handler->add($notification);
		$this->assertSame(1, $this->handler->count($limitedNotification));

		// Get and count
		$notifications = $this->handler->get($limitedNotification);
		$this->assertCount(1, $notifications);
		$notificationId = array_key_first($notifications);

		// Get with wrong user
		try {
			$this->handler->getById($notificationId, 'test_user2');
			$this->fail('Exception of type NotificationNotFoundException expected');
		} catch (\Exception $e) {
			$this->assertInstanceOf(NotificationNotFoundException::class, $e);
		}

		// Delete with wrong user
		$this->handler->deleteById($notificationId, 'test_user2', $notification);
		$this->assertSame(1, $this->handler->count($limitedNotification), 'Wrong notification count for user1 after trying to delete for user2');

		// Get with correct user
		$getNotification = $this->handler->getById($notificationId, 'test_user1');
		$this->assertInstanceOf(INotification::class, $getNotification);

		// Delete and count
		$this->handler->deleteById($notificationId, 'test_user1');
		$this->assertSame(0, $this->handler->count($limitedNotification), 'Wrong notification count for user1 after deleting');
	}

	public function testFilterByObject(): void {
		$user = 'test_user1';
		foreach ([['reminder', 'token1'], ['reminder', 'token2'], ['chat', 'token1']] as [$objectType, $objectId]) {
			$this->handler->add($this->getNotification([
				'getApp' => 'testing_notifications',
				'getUser' => $user,
				'getDateTime' => new \DateTime(),
				'getObjectType' => $objectType,
				'getObjectId' => $objectId,
				'getSubject' => 'subject',
				'getSubjectParameters' => [],
				'getMessage' => 'message',
				'getMessageParameters' => [],
				'getLink' => 'https://example.tld/notification',
				'getIcon' => 'https://example.tld/icon',
				'getActions' => [],
			]));
		}

		$unfiltered = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => $user,
		]);
		$this->assertCount(3, $this->handler->get($unfiltered), 'Wrong notification count without an object filter');

		$byTypeOnly = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => $user,
			'getObjectType' => 'reminder',
			'getObjectId' => Handler::FILTER_OBJECT_TYPE_ONLY,
		]);
		$this->assertCount(2, $this->handler->get($byTypeOnly), 'Wrong notification count when filtering by object type only');
		$this->assertSame(2, $this->handler->count($byTypeOnly), 'Wrong notification count when filtering by object type only');

		$byTypeAndId = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => $user,
			'getObjectType' => 'reminder',
			'getObjectId' => 'token1',
		]);
		$this->assertCount(1, $this->handler->get($byTypeAndId), 'Wrong notification count when filtering by object type and id');

		$byOtherType = $this->getNotification([
			'getApp' => 'testing_notifications',
			'getUser' => $user,
			'getObjectType' => 'chat',
			'getObjectId' => Handler::FILTER_OBJECT_TYPE_ONLY,
		]);
		$this->assertCount(1, $this->handler->get($byOtherType), 'Wrong notification count when filtering by another object type');
	}

	protected function getNotification(array $values = []): INotification&MockObject {
		$notification = $this->getMockBuilder(INotification::class)
			->getMock();

		foreach ($values as $method => $returnValue) {
			if ($method === 'getActions') {
				$actions = [];
				foreach ($returnValue as $actionData) {
					$action = $this->getMockBuilder(IAction::class)
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

		$defaultDateTime = new \DateTime();
		$defaultDateTime->setTimestamp(0);
		$defaultValues = [
			'getApp' => '',
			'getUser' => '',
			'getDateTime' => $defaultDateTime,
			'getObjectType' => '',
			'getObjectId' => '',
			'getSubject' => '',
			'getSubjectParameters' => [],
			'getMessage' => '',
			'getMessageParameters' => [],
			'getLink' => '',
			'getIcon' => '',
			'getActions' => [],
		];
		foreach ($defaultValues as $method => $returnValue) {
			if (isset($values[$method])) {
				continue;
			}

			$notification->expects($this->any())
				->method($method)
				->willReturn($returnValue);
		}

		$defaultValues = [
			'setApp',
			'setUser',
			'setDateTime',
			'setObject',
			'setSubject',
			'setMessage',
			'setLink',
			'setIcon',
			'addAction',
		];
		foreach ($defaultValues as $method) {
			$notification->expects($this->any())
				->method($method)
				->willReturnSelf();
		}

		return $notification;
	}
}
