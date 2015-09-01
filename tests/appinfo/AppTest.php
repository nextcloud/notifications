<?php

/**
 * ownCloud - Notification
 *
 * @author Joas Schilling
 * @copyright 2014 Joas Schilling nickvergessen@owncloud.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Notification\Tests;

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
