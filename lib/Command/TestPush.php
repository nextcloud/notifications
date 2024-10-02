<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Command;

use OCA\Notifications\App;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestPush extends Command {
	public function __construct(
		protected ITimeFactory $timeFactory,
		protected IUserManager $userManager,
		protected IManager $notificationManager,
		protected App $app,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('notification:test-push')
			->setDescription('Generate a notification for the given user')
			->addArgument(
				'user-id',
				InputArgument::REQUIRED,
				'User ID of the user to notify'
			)
			->addOption(
				'talk',
				null,
				InputOption::VALUE_NONE,
				'Test talk devices'
			)
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		if (!$this->notificationManager->isFairUseOfFreePushService()) {
			$output->writeln('<error>We want to keep offering our push notification service for free, but large</error>');
			$output->writeln('<error>users overload our infrastructure. For this reason we have to rate-limit the</error>');
			$output->writeln('<error>use of push notifications. If you need this feature, consider using Nextcloud Enterprise.</error>');
			return 1;
		}

		$userId = $input->getArgument('user-id');
		$subject = 'Testing push notifications';

		$user = $this->userManager->get($userId);
		if (!$user instanceof IUser) {
			$output->writeln('Unknown user');
			return 1;
		}

		$notification = $this->notificationManager->createNotification();
		$datetime = $this->timeFactory->getDateTime();
		$app = $input->getOption('talk') ? 'admin_notification_talk' : 'admin_notifications';

		try {
			$notification->setApp($app)
				->setUser($user->getUID())
				->setDateTime($datetime)
				->setObject('admin_notifications', dechex($datetime->getTimestamp()))
				->setSubject('cli', [$subject]);

			$this->app->setOutput($output);
			$this->notificationManager->notify($notification);
		} catch (\InvalidArgumentException) {
			$output->writeln('Error while sending the notification');
			return 1;
		}

		return 0;
	}
}
