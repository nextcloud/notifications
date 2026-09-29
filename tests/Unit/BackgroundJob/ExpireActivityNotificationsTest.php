<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit\BackgroundJob;

use OCA\Notifications\BackgroundJob\ExpireActivityNotifications;
use OCA\Notifications\Handler;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ExpireActivityNotificationsTest extends TestCase {
	protected ITimeFactory&MockObject $timeFactory;
	protected IConfig&MockObject $config;
	protected Handler&MockObject $handler;
	protected ExpireActivityNotifications $job;

	protected function setUp(): void {
		parent::setUp();

		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->config = $this->createMock(IConfig::class);
		$this->handler = $this->createMock(Handler::class);

		$this->job = new ExpireActivityNotifications(
			$this->timeFactory,
			$this->config,
			$this->handler,
		);
	}

	public static function dataRun(): array {
		return [
			'default expiry' => [null, 365],
			'configured expiry' => [10, 10],
			'zero is clamped to one day' => [0, 1],
		];
	}

	#[DataProvider(methodName: 'dataRun')]
	public function testRun(?int $configuredExpireDays, int $expectedExpireDays): void {
		$now = 1_700_000_000;
		$this->timeFactory->expects($this->once())
			->method('getTime')
			->willReturn($now);

		$this->config->expects($this->once())
			->method('getSystemValueInt')
			->with('activity_expire_days', 365)
			->willReturn($configuredExpireDays ?? 365);

		$this->handler->expects($this->once())
			->method('expireOlderThan')
			->with('activity_notification', $now - (60 * 60 * 24 * $expectedExpireDays));

		self::invokePrivate($this->job, 'run', [null]);
	}
}
