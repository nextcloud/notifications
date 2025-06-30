<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications\Tests\Unit\AppInfo;

use OCA\Notifications\App;
use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Capabilities;
use OCA\Notifications\Controller\EndpointController;
use OCA\Notifications\Controller\PushController;
use OCA\Notifications\Handler;
use OCA\Notifications\Push;
use OCP\AppFramework\OCSController;
use OCP\Capabilities\ICapability;
use OCP\Notification\IApp;
use OCP\Server;
use Test\TestCase;

/**
 * Class ApplicationTest
 *
 * @group DB
 * @package OCA\Notifications\Tests\AppInfo
 */
class ApplicationTest extends TestCase {
	protected Application $app;

	protected function setUp(): void {
		parent::setUp();
		$this->app = new Application();
	}

	public static function dataContainerQuery(): array {
		return [
			// lib/
			[App::class, IApp::class],
			[Capabilities::class, ICapability::class],
			[Handler::class],
			[Push::class],

			// Controller/
			[EndpointController::class, OCSController::class],
			[PushController::class, OCSController::class],
		];
	}

	/**
	 * @dataProvider dataContainerQuery
	 */
	public function testContainerQuery(string $service, ?string $expected = null): void {
		if ($expected === null) {
			$expected = $service;
		}
		$this->assertInstanceOf($expected, Server::get($service));
	}
}
