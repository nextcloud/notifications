<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
use Test\TestCase;

class NotifierTest extends TestCase {
	protected IFactory&MockObject $factory;
	protected IURLGenerator&MockObject $urlGenerator;
	protected IUserManager&MockObject $userManager;
	protected IRootFolder&MockObject $rootFolder;
	protected IL10N&MockObject $l;
	protected AdminNotifications $notifier;

	protected function setUp(): void {
		parent::setUp();

		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->l = $this->createMock(IL10N::class);
		$this->l->expects($this->any())
			->method('t')
			->willReturnCallback(fn ($string, $args) => vsprintf($string, $args));
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
		/** @var INotification&MockObject $notification */
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
		/** @var INotification&MockObject $notification */
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
		/** @var INotification&MockObject $notification */
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
