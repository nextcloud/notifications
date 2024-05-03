<?php
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
use OCA\Notifications\Tests\Unit\TestCase;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\OCSController;
use OCP\Notification\IApp;

/**
 * Class ApplicationTest
 *
 * @group DB
 * @package OCA\Notifications\Tests\AppInfo
 */
class ApplicationTest extends TestCase {
	/** @var Application */
	protected $app;

	/** @var IAppContainer */
	protected $container;

	protected function setUp(): void {
		parent::setUp();
		$this->app = new Application();
		$this->container = $this->app->getContainer();
	}

	public function testContainerAppName() {
		$this->app = new Application();
		$this->assertEquals('notifications', $this->container->getAppName());
	}

	public function dataContainerQuery() {
		return [
			// lib/
			[App::class],
			[App::class, IApp::class],
			[Capabilities::class],
			[Handler::class],
			[Push::class],

			// Controller/
			[EndpointController::class],
			[EndpointController::class, OCSController::class],
			[PushController::class],
			[PushController::class, OCSController::class],
		];
	}

	/**
	 * @dataProvider dataContainerQuery
	 * @param string $service
	 * @param string $expected
	 */
	public function testContainerQuery($service, $expected = null) {
		if ($expected === null) {
			$expected = $service;
		}
		$this->assertTrue($this->container->query($service) instanceof $expected);
	}
}
