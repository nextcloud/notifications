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

namespace OCA\Notifications\Tests;

use OCA\Notifications\Tests\TestCase;

class AppTest extends TestCase {
	/** @var \OCP\Notification\IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $manager;

	protected function setUp() {
		parent::setUp();

		$this->manager = $this->getMockBuilder('OCP\Notification\IManager')
			->disableOriginalConstructor()
			->getMock();

		$this->overwriteService('NotificationManager', $this->manager);
	}

	protected function tearDown() {
		$this->restoreService('NotificationManager');

		parent::tearDown();
	}

	public function testRegisterApp() {
		$this->manager->expects($this->once())
			->method('registerApp')
			->willReturnCallback(function($closure) {
				$this->assertInstanceOf('\Closure', $closure);
				$navigation = $closure();
				$this->assertInstanceOf('\OCA\Notifications\App', $navigation);
			});

		include(__DIR__ . '/../../appinfo/app.php');
	}
}
