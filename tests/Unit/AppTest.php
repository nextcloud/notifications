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

namespace OCA\Notifications\Tests\Unit;

use OCA\Notifications\App;
use OCA\Notifications\Handler;
use OCA\Notifications\Push;
use OCP\Notification\INotification;

class AppTest extends TestCase {
	/** @var Handler|\PHPUnit_Framework_MockObject_MockObject */
	protected $handler;
	/** @var Push|\PHPUnit_Framework_MockObject_MockObject */
	protected $push;
	/** @var INotification|\PHPUnit_Framework_MockObject_MockObject */
	protected $notification;

	/** @var App */
	protected $app;

	protected function setUp(): void {
		parent::setUp();

		$this->handler = $this->createMock(Handler::class);
		$this->push = $this->createMock(Push::class);
		$this->notification = $this->createMock(INotification::class);

		$this->app = new App(
			$this->handler,
			$this->push
		);
	}

	public function dataNotify() {
		return [
			[23, 'user1'],
			[42, 'user2'],
		];
	}

	/**
	 * @dataProvider dataNotify
	 *
	 * @param int $id
	 * @param string $user
	 */
	public function testNotify($id, $user) {
		$this->notification->expects($this->once())
			->method('getUser')
			->willReturn($user);

		$this->handler->expects($this->once())
			->method('add')
			->with($this->notification)
			->willReturn($id);
		$this->handler->expects($this->once())
			->method('getById')
			->with($id, $user)
			->willReturn($this->notification);
		$this->push->expects($this->once())
			->method('pushToDevice')
			->with($id, $this->notification);

		$this->app->notify($this->notification);
	}

	public function dataGetCount() {
		return [
			[23],
			[42],
		];
	}

	/**
	 * @dataProvider dataGetCount
	 *
	 * @param int $count
	 */
	public function testGetCount($count) {
		$this->handler->expects($this->once())
			->method('count')
			->with($this->notification)
			->willReturn($count);

		$this->assertSame($count, $this->app->getCount($this->notification));
	}

	public function testMarkProcessed() {
		$this->handler->expects($this->once())
			->method('delete')
			->with($this->notification);

		$this->app->markProcessed($this->notification);
	}
}
