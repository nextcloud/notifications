<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications\Tests\Unit;

use OCA\Notifications\Capabilities;
use OCP\AppFramework\Services\IAppConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\TestCase;

class CapabilitiesTest extends TestCase {
	public static function dataGetCapabilities(): array {
		return [
			[false, false, [
				'devices',
				'object-data',
				'delete',
			]],
			[false, true, [
				'devices',
				'object-data',
				'delete',
			]],
			[true, false, [
				'devices',
				'object-data',
				'delete',
				'webpush',
			]],
			[true, true, [
				'devices',
				'object-data',
				'delete',
				'webpush',
				'webpush-browsers',
			]],
		];
	}

	#[DataProvider(methodName: 'dataGetCapabilities')]
	public function testGetCapabilities(bool $webpush, bool $webpushBrowser, array $expected): void {
		$appConfig = $this->createMock(IAppConfig::class);
		$appConfig->method('getAppValueBool')
			->willReturnMap([
				['webpush_enabled', false, $webpush],
				['webpush_browsers_enabled', false, $webpushBrowser],
			]);

		$capabilities = new Capabilities($appConfig);

		$this->assertSame([
			'notifications' => [
				'ocs-endpoints' => [
					'list',
					'get',
					'delete',
					'delete-all',
					'icons',
					'rich-strings',
					'action-web',
					'user-status',
					'exists',
					'test-push',
				],
				'push' => $expected,
				'admin-notifications' => [
					'ocs',
					'cli',
				],
			],
		], $capabilities->getCapabilities());
	}
}
