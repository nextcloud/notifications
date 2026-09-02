<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit;

use OCA\Notifications\Handler;
use OCA\Notifications\MailNotifications;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Config\IUserConfig;
use OCP\Config\ValueType;
use OCP\Defaults;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Mail\IMailer;
use OCP\Notification\IManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class MailNotificationsTest extends TestCase {
	protected IConfig&MockObject $systemConfig;
	protected IUserConfig&MockObject $userConfig;
	protected IAppConfig&MockObject $appConfig;
	protected IManager&MockObject $manager;
	protected Handler&MockObject $handler;
	protected IUserManager&MockObject $userManager;
	protected LoggerInterface&MockObject $logger;
	protected IMailer&MockObject $mailer;
	protected IURLGenerator&MockObject $urlGenerator;
	protected Defaults&MockObject $defaults;
	protected IFactory&MockObject $l10nFactory;
	protected IDateTimeFormatter&MockObject $dateFormatter;
	protected ITimeFactory&MockObject $timeFactory;
	protected SettingsMapper&MockObject $settingsMapper;
	protected MailNotifications $mailNotifications;

	protected function setUp(): void {
		parent::setUp();

		$this->systemConfig = $this->createMock(IConfig::class);
		$this->userConfig = $this->createMock(IUserConfig::class);
		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->manager = $this->createMock(IManager::class);
		$this->handler = $this->createMock(Handler::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->mailer = $this->createMock(IMailer::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->defaults = $this->createMock(Defaults::class);
		$this->l10nFactory = $this->createMock(IFactory::class);
		$this->dateFormatter = $this->createMock(IDateTimeFormatter::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->settingsMapper = $this->createMock(SettingsMapper::class);

		$this->mailNotifications = new MailNotifications(
			$this->systemConfig,
			$this->userConfig,
			$this->appConfig,
			$this->manager,
			$this->handler,
			$this->userManager,
			$this->logger,
			$this->mailer,
			$this->urlGenerator,
			$this->defaults,
			$this->l10nFactory,
			$this->dateFormatter,
			$this->timeFactory,
			$this->settingsMapper,
		);
	}

	public static function dataSendEmailsSkipsDisabledUsers(): array {
		return [
			'disabled user is skipped' => [['user1' => false], ['user2']],
			'all users disabled' => [['user1' => false, 'user2' => false], []],
			'enabled users are processed' => [['user1' => true, 'user2' => true], ['user1', 'user2']],
			'missing value means enabled' => [[], ['user1', 'user2']],
		];
	}

	/**
	 * @param array<string, bool> $userEnabled value of core/enabled per user
	 * @param string[] $expectedUserIds users the notifications are read for
	 */
	#[DataProvider('dataSendEmailsSkipsDisabledUsers')]
	public function testSendEmailsSkipsDisabledUsers(array $userEnabled, array $expectedUserIds): void {
		$sendTime = 2000;
		$batchTime = 3600;

		$userSettings = [];
		foreach (['user1', 'user2'] as $userId) {
			$settings = new Settings();
			$settings->setUserId($userId);
			$settings->setBatchTime($batchTime);
			$settings->setLastSendId(0);
			$settings->setNextSendTime(1000);
			$userSettings[$userId] = $settings;
		}

		$this->settingsMapper->method('getUsersByNextSendTime')
			->with(MailNotifications::BATCH_SIZE_WEB)
			->willReturn(array_values($userSettings));

		$this->userConfig->method('getValuesByUsers')
			->willReturnCallback(static function (string $app, string $key, ?ValueType $typedAs) use ($userEnabled): array {
				if ($key === 'enabled') {
					self::assertSame(ValueType::BOOL, $typedAs);
					return $userEnabled;
				}
				return [];
			});

		$this->appConfig->method('getAppValueInt')
			->with('setting_batchtime')
			->willReturn(Settings::EMAIL_SEND_HOURLY);
		$this->systemConfig->method('getSystemValueString')
			->willReturn('');

		$readUserIds = [];
		$this->handler->method('getAfterId')
			->willReturnCallback(static function (int $lastSendId, string $userId) use (&$readUserIds): array {
				$readUserIds[] = $userId;
				return [];
			});

		$this->mailer->expects($this->never())
			->method('send');

		$this->mailNotifications->sendEmails(MailNotifications::BATCH_SIZE_WEB, $sendTime);

		$this->assertSame($expectedUserIds, $readUserIds);
		foreach ($userSettings as $settings) {
			$this->assertSame($sendTime + $batchTime, $settings->getNextSendTime());
		}
	}

	public function testSendEmailsKeepsNextSendTimeOfDisabledUserInFuture(): void {
		$sendTime = 2000;
		$futureSendTime = 5000;

		$settings = new Settings();
		$settings->setUserId('user1');
		$settings->setBatchTime(3600);
		$settings->setLastSendId(0);
		$settings->setNextSendTime($futureSendTime);

		$this->settingsMapper->method('getUsersByNextSendTime')
			->willReturn([$settings]);
		$this->userConfig->method('getValuesByUsers')
			->willReturnCallback(static fn (string $app, string $key): array => $key === 'enabled' ? ['user1' => false] : []);
		$this->appConfig->method('getAppValueInt')
			->willReturn(Settings::EMAIL_SEND_HOURLY);
		$this->systemConfig->method('getSystemValueString')
			->willReturn('');

		$this->handler->expects($this->never())
			->method('getAfterId');
		$this->settingsMapper->expects($this->never())
			->method('update');

		$this->mailNotifications->sendEmails(MailNotifications::BATCH_SIZE_WEB, $sendTime);

		$this->assertSame($futureSendTime, $settings->getNextSendTime());
	}
}
