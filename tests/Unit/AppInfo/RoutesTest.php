<?php
/**
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications\Tests\Unit\AppInfo;

use OCA\Notifications\Tests\Unit\TestCase;

/**
 * Class RoutesTest
 *
 * @group DB
 * @package OCA\Notifications\Tests\AppInfo
 */
class RoutesTest extends TestCase {
	public function testRoutes() {
		$routes = include __DIR__ . '/../../../appinfo/routes.php';
		$this->assertIsArray($routes);
		$this->assertCount(1, $routes);
		$this->assertArrayHasKey('ocs', $routes);
		$this->assertIsArray($routes['ocs']);
		$this->assertCount(9, $routes['ocs']);
	}
}
