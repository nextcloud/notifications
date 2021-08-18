<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
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
		$this->assertCount(8, $routes['ocs']);
	}
}
