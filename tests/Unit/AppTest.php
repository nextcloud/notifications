<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications\Tests\Unit;

use OCA\Notifications\App;
use OCA\Notifications\Handler;
use OCA\Notifications\Push;
use OCP\Notification\INotification;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AppTest extends TestCase {
	protected Handler&MockObject $handler;
	protected Push&MockObject $push;
	protected INotification&MockObject $notification;
	protected LoggerInterface&MockObject $logger;
	protected App $app;

	protected function setUp(): void {
		parent::setUp();

		$this->handler = $this->createMock(Handler::class);
		$this->push = $this->createMock(Push::class);
		$this->notification = $this->createMock(INotification::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->app = new App(
			$this->handler,
			$this->push,
			$this->logger,
		);
	}

	public static function dataNotify(): array {
		return [
			[23],
			[42],
		];
	}

	/**
	 * @dataProvider dataNotify
	 *
	 * @param int $id
	 */
	public function testNotify(int $id): void {
		$this->handler->expects($this->once())
			->method('add')
			->with($this->notification)
			->willReturn($id);
		$this->push->expects($this->once())
			->method('pushToDevice')
			->with($id, $this->notification);

		$this->app->notify($this->notification);
	}

	public static function dataGetCount(): array {
		return [
			[23],
			[42],
		];
	}

	/**
	 * @dataProvider dataGetCount
	 */
	public function testGetCount(int $count): void {
		$this->handler->expects($this->once())
			->method('count')
			->with($this->notification)
			->willReturn($count);

		$this->assertSame($count, $this->app->getCount($this->notification));
	}

	public function testMarkProcessed(): void {
		$this->handler->expects($this->once())
			->method('delete')
			->with($this->notification);

		$this->app->markProcessed($this->notification);
	}
}
