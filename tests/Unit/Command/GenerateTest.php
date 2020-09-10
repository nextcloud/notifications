<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Notifications\Tests\Unit\Command;

use OCA\Notifications\Command\Generate;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateTest
 *
 * @package OCA\Notifications\Tests\Unit\Command
 * @group DB
 */
class GenerateTest extends \Test\TestCase {
	/** @var ITimeFactory|\PHPUnit_Framework_MockObject_MockObject */
	protected $timeFactory;
	/** @var IUserManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $userManager;
	/** @var IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $notificationManager;
	/** @var Generate */
	protected $command;

	protected function setUp(): void {
		parent::setUp();

		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->notificationManager = $this->createMock(IManager::class);

		$this->command = new Generate(
			$this->timeFactory,
			$this->userManager,
			$this->notificationManager
		);
	}

	public function dataExecute() {
		return [
			['user', '', '', false, null, false, null, false, 1],
			['user', '', '', false, null, false, 'user', false, 1],
			['user', str_repeat('a', 256), '', false, null, false, 'user', false, 1],
			['user', 'short', '', true, false, false, 'user', true, 0],
			['user', 'short', str_repeat('a', 4001), false, null, false, 'user', false, 1],
			['user', 'short', str_repeat('a', 4000), true, false, true, 'user',  true, 0],
			['user', 'short', 'long', true, true, true, 'user', true, 1],
		];
	}

	/**
	 * @dataProvider dataExecute
	 * @param string $userId
	 * @param string $short
	 * @param string $long
	 * @param bool $createNotification
	 * @param bool $notifyThrows
	 * @param bool $validLong
	 * @param string|null $user
	 * @param bool $isCreated
	 * @param int $exitCode
	 */
	public function testExecute($userId, $short, $long, $createNotification, $notifyThrows, $validLong, $user, $isCreated, $exitCode) {
		if ($user !== null) {
			$u = $this->createMock(IUser::class);
			$u->expects($createNotification ? $this->once() : $this->never())
				->method('getUID')
				->willReturn($user);
		} else {
			$u = null;
		}
		$this->userManager->expects($this->any())
			->method('get')
			->with($userId)
			->willReturn($u);

		$dateTime = new \DateTime();
		$this->timeFactory->expects(!$isCreated ? $this->never() : $this->once())
			->method('getDateTime')
			->willReturn($dateTime);

		if ($createNotification) {
			$n = $this->createMock(INotification::class);
			$n->expects($this->once())
				->method('setApp')
				->with('admin_notifications')
				->willReturnSelf();
			$n->expects($this->once())
				->method('setUser')
				->with($user)
				->willReturnSelf();
			$n->expects($this->once())
				->method('setDateTime')
				->with($dateTime)
				->willReturnSelf();
			$n->expects($this->once())
				->method('setObject')
				->with('admin_notifications', dechex($dateTime->getTimestamp()))
				->willReturnSelf();
			$n->expects($this->once())
				->method('setSubject')
				->with('cli', [$short])
				->willReturnSelf();
			if ($validLong) {
				$n->expects($this->once())
					->method('setMessage')
					->with('cli', [$long])
					->willReturnSelf();
			} else {
				$n->expects($this->never())
					->method('setMessage');
			}

			$this->notificationManager->expects($this->once())
				->method('createNotification')
				->willReturn($n);

			if ($notifyThrows === null) {
				$this->notificationManager->expects($this->never())
					->method('notify');
			} elseif ($notifyThrows === false) {
				$this->notificationManager->expects($this->once())
					->method('notify')
					->with($n);
			} elseif ($notifyThrows === true) {
				$this->notificationManager->expects($this->once())
					->method('notify')
					->willThrowException(new \InvalidArgumentException());
			}
		}

		$input = $this->createMock(InputInterface::class);
		$input->expects($this->exactly(2))
			->method('getArgument')
			->willReturnMap([
				['user-id', $userId],
				['short-message', $short],
			]);
		$input->expects($this->exactly(1))
			->method('getOption')
			->willReturnMap([
				['long-message', $long],
			]);
		$output = $this->createMock(OutputInterface::class);

		$return = self::invokePrivate($this->command, 'execute', [$input, $output]);
		$this->assertSame($exitCode, $return);
	}
}
