<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2017, Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @license AGPL-3.0-or-later
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
	/** @var ITimeFactory */
	protected $timeFactory;
	/** @var IUserManager */
	protected $userManager;
	/** @var IManager */
	protected $notificationManager;
	/** @var App */
	protected $app;

	public function __construct(
		ITimeFactory $timeFactory,
		IUserManager $userManager,
		IManager $notificationManager,
		App $app) {
		parent::__construct();

		$this->timeFactory = $timeFactory;
		$this->userManager = $userManager;
		$this->notificationManager = $notificationManager;
		$this->app = $app;
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
		} catch (\InvalidArgumentException $e) {
			$output->writeln('Error while sending the notification');
			return 1;
		}

		return 0;
	}
}
