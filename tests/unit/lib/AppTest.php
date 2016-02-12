<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
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

namespace OCA\Notifications\Tests\Lib;


use OCA\Notifications\App;
use OCA\Notifications\Tests\Unit\TestCase;

class AppTest extends TestCase {
	/** @var \OCA\Notifications\Handler|\PHPUnit_Framework_MockObject_MockObject */
	protected $handler;

	/** @var \OCP\Notification\INotification|\PHPUnit_Framework_MockObject_MockObject */
	protected $notification;

	/** @var \OCA\Notifications\App */
	protected $app;

	protected function setUp() {
		parent::setUp();

		$this->handler = $this->getMockBuilder('OCA\Notifications\Handler')
			->disableOriginalConstructor()
			->getMock();

		$this->notification = $this->getMockBuilder('OCP\Notification\INotification')
			->disableOriginalConstructor()
			->getMock();

		$this->app = new App(
			$this->handler
		);
	}

	public function testNotify() {
		$this->handler->expects($this->once())
			->method('add')
			->with($this->notification);

		$this->app->notify($this->notification);
	}

	public function testGetCount() {
		$this->handler->expects($this->once())
			->method('count')
			->with($this->notification)
			->willReturn(42);

		$this->assertSame(42, $this->app->getCount($this->notification));
	}

	public function testMarkProcessed() {
		$this->handler->expects($this->once())
			->method('delete')
			->with($this->notification);

		$this->app->markProcessed($this->notification);
	}
}
