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

namespace OCA\Notifications\Tests\Unit\Notifier;

use OCA\Notifications\Notifier\AdminNotifications;
use OCP\Files\IRootFolder;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\UnknownNotificationException;
use PHPUnit\Framework\MockObject\MockObject;

class NotifierTest extends \Test\TestCase {
	protected IFactory|MockObject $factory;
	protected IURLGenerator|MockObject $urlGenerator;
	protected IUserManager|MockObject $userManager;
	protected IRootFolder|MockObject $rootFolder;
	protected IL10N|MockObject $l;
	protected AdminNotifications $notifier;

	protected function setUp(): void {
		parent::setUp();

		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->l = $this->createMock(IL10N::class);
		$this->l->expects($this->any())
			->method('t')
			->willReturnCallback(function ($string, $args) {
				return vsprintf($string, $args);
			});
		$this->factory = $this->createMock(IFactory::class);
		$this->factory->expects($this->any())
			->method('get')
			->willReturn($this->l);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->rootFolder = $this->createMock(IRootFolder::class);

		$this->notifier = new AdminNotifications(
			$this->factory,
			$this->urlGenerator,
			$this->userManager,
			$this->rootFolder
		);
	}

	public function testPrepareWrongApp(): void {
		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);

		$notification->expects($this->exactly(2))
			->method('getApp')
			->willReturn('notifications');
		$notification->expects($this->never())
			->method('getSubject');

		$this->expectException(UnknownNotificationException::class);
		$this->expectExceptionMessage('app');
		$this->notifier->prepare($notification, 'en');
	}

	public function testPrepareWrongSubject(): void {
		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);

		$notification->expects($this->once())
			->method('getApp')
			->willReturn('admin_notifications');
		$notification->expects($this->once())
			->method('getSubject')
			->willReturn('wrong subject');

		$this->expectException(UnknownNotificationException::class);
		$this->expectExceptionMessage('subject');
		$this->notifier->prepare($notification, 'en');
	}

	public static function dataPrepare(): array {
		return [
			['ocs', ['subject'], ['message'], true],
		];
	}

	/**
	 * @dataProvider dataPrepare
	 */
	public function testPrepare(string $subject, array $subjectParams, array $messageParams, bool $setMessage): void {
		/** @var INotification|MockObject $notification */
		$notification = $this->createMock(INotification::class);

		$notification->expects($this->once())
			->method('getApp')
			->willReturn('admin_notifications');
		$notification->expects($this->once())
			->method('getSubject')
			->willReturn($subject);
		$notification->expects($this->once())
			->method('getSubjectParameters')
			->willReturn($subjectParams);
		$notification->expects($this->once())
			->method('getMessageParameters')
			->willReturn($messageParams);

		$notification->expects($this->once())
			->method('setParsedSubject')
			->with($subjectParams[0])
			->willReturnSelf();

		if ($setMessage) {
			$notification->expects($this->once())
				->method('setParsedMessage')
				->with($messageParams[0])
				->willReturnSelf();
		} else {
			$notification->expects($this->never())
				->method('setParsedMessage');
		}

		$this->urlGenerator->expects($this->once())
			->method('imagePath')
			->with('notifications', 'notifications-dark.svg')
			->willReturn('icon-url');
		$this->urlGenerator->expects($this->once())
			->method('getAbsoluteURL')
			->with('icon-url')
			->willReturn('absolute-icon-url');
		$notification->expects($this->once())
			->method('setIcon')
			->with('absolute-icon-url')
			->willReturnSelf();

		$return = $this->notifier->prepare($notification, 'en');

		$this->assertEquals($notification, $return);
	}
}
