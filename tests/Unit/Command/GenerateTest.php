<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit\Command;

use OCA\Notifications\App;
use OCA\Notifications\Command\Generate;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\RichObjectStrings\IValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Test\TestCase;

/**
 * Class GenerateTest
 *
 * @package OCA\Notifications\Tests\Unit\Command
 * @group DB
 */
class GenerateTest extends TestCase {
	protected ITimeFactory&MockObject $timeFactory;
	protected IUserManager&MockObject $userManager;
	protected IManager&MockObject $notificationManager;
	protected IValidator&MockObject $richValidator;
	protected App&MockObject $notificationApp;
	protected Generate $command;

	protected function setUp(): void {
		parent::setUp();

		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->notificationManager = $this->createMock(IManager::class);
		$this->richValidator = $this->createMock(IValidator::class);
		$this->notificationApp = $this->createMock(App::class);

		$this->command = new Generate(
			$this->timeFactory,
			$this->userManager,
			$this->notificationManager,
			$this->richValidator,
			$this->notificationApp,
		);
	}

	public static function dataExecute(): array {
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
	 */
	public function testExecute(string $userId, string $short, string $long, bool $createNotification, ?bool $notifyThrows, bool $validLong, ?string $user, bool $isCreated, int $exitCode): void {
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
		$input->method('getArgument')
			->willReturnMap([
				['user-id', $userId],
				['short-message', $short],
			]);
		$input->method('getOption')
			->willReturnMap([
				['long-message', $long],
				['dummy', false],
			]);
		$output = $this->createMock(OutputInterface::class);

		$return = self::invokePrivate($this->command, 'execute', [$input, $output]);
		$this->assertSame($exitCode, $return);
	}
}
