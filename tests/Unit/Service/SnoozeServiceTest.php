<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit\Service;

use OCA\Notifications\Exceptions\InvalidSnoozeException;
use OCA\Notifications\Handler;
use OCA\Notifications\Push;
use OCA\Notifications\Service\SnoozeService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Notification\INotification;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class SnoozeServiceTest extends TestCase {
	protected Handler&MockObject $handler;
	protected Push&MockObject $push;
	protected ITimeFactory&MockObject $timeFactory;
	protected SnoozeService $service;

	protected function setUp(): void {
		parent::setUp();

		$this->handler = $this->createMock(Handler::class);
		$this->push = $this->createMock(Push::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);

		$this->service = new SnoozeService(
			$this->handler,
			$this->push,
			$this->timeFactory,
		);
	}

	public function testSnoozeRejectsPastTimestamp(): void {
		$this->timeFactory->method('getTime')->willReturn(1000);
		$notification = $this->createMock(INotification::class);

		$this->handler->expects($this->never())->method('snooze');
		$this->push->expects($this->never())->method('pushDeleteToDevice');

		$this->expectException(InvalidSnoozeException::class);
		$this->service->snooze($notification, 42, 'user1', 1000);
	}

	public function testSnoozeRejectsTooFarInTheFuture(): void {
		$this->timeFactory->method('getTime')->willReturn(1000);
		$notification = $this->createMock(INotification::class);

		$this->handler->expects($this->never())->method('snooze');
		$this->push->expects($this->never())->method('pushDeleteToDevice');

		$this->expectException(InvalidSnoozeException::class);
		$this->service->snooze($notification, 42, 'user1', 1000 + SnoozeService::MAX_SNOOZE + 1);
	}

	public function testSnoozePushesADelete(): void {
		$this->timeFactory->method('getTime')->willReturn(1000);
		$notification = $this->createMock(INotification::class);
		$notification->method('getApp')->willReturn('files');

		$this->handler->expects($this->once())
			->method('snooze')
			->with(42, 'user1', 2000, $notification);
		$this->push->expects($this->once())
			->method('pushDeleteToDevice')
			->with('user1', [42], 'files');

		$this->service->snooze($notification, 42, 'user1', 2000);
	}

	public function testWakeUpInsertsBeforeDeletingAndPushesTheNewId(): void {
		$now = new \DateTime();
		$this->timeFactory->method('getDateTime')->willReturn($now);
		$notification = $this->createMock(INotification::class);
		$notification->expects($this->once())
			->method('setDateTime')
			->with($now)
			->willReturnSelf();

		$calls = [];
		$this->handler->expects($this->once())
			->method('add')
			->with($notification)
			->willReturnCallback(function () use (&$calls) {
				$calls[] = 'add';
				return 99;
			});
		$this->handler->expects($this->once())
			->method('deleteIds')
			->with([42])
			->willReturnCallback(function () use (&$calls) {
				$calls[] = 'deleteIds';
			});
		$this->handler->expects($this->never())->method('deleteById');

		$this->push->expects($this->once())
			->method('pushToDevice')
			->with(99, $notification);

		$newId = $this->service->wakeUp(42, $notification);

		$this->assertSame(99, $newId);
		$this->assertSame(['add', 'deleteIds'], $calls);
	}

	public function testWakeUpDueDefersAndFlushesPayloadsOnce(): void {
		$notificationA = $this->createMock(INotification::class);
		$notificationB = $this->createMock(INotification::class);

		$this->handler->expects($this->once())
			->method('getSnoozedUntil')
			->with(5000, 1000)
			->willReturn([1 => $notificationA, 2 => $notificationB]);

		$this->push->method('isDeferring')->willReturn(false);
		$this->push->expects($this->once())->method('deferPayloads');
		$this->push->expects($this->once())->method('flushPayloads');
		$this->push->expects($this->exactly(2))->method('pushToDevice');

		$this->handler->expects($this->exactly(2))->method('add')->willReturn(101);
		$this->handler->expects($this->exactly(2))->method('deleteIds');

		$count = $this->service->wakeUpDue(5000, 1000);

		$this->assertSame(2, $count);
	}

	public function testWakeUpDueDoesNotFlushWhenAlreadyDeferring(): void {
		$this->handler->method('getSnoozedUntil')->willReturn([]);
		$this->push->method('isDeferring')->willReturn(true);

		$this->push->expects($this->never())->method('deferPayloads');
		$this->push->expects($this->never())->method('flushPayloads');

		$this->service->wakeUpDue(5000, 1000);
	}
}
