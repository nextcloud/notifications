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

use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Command {
	/** @var ITimeFactory */
	protected $timeFactory;

	/** @var IUserManager */
	protected $userManager;

	/** @var IManager */
	protected $notificationManager;

	public function __construct(ITimeFactory $timeFactory,
		IUserManager $userManager,
		IManager $notificationManager) {
		parent::__construct();

		$this->timeFactory = $timeFactory;
		$this->userManager = $userManager;
		$this->notificationManager = $notificationManager;
	}

	protected function configure(): void {
		$this
			->setName('notification:generate')
			->setDescription('Generate a notification for the given user')
			->addArgument(
				'user-id',
				InputArgument::REQUIRED,
				'User ID of the user to notify'
			)
			->addArgument(
				'short-message',
				InputArgument::REQUIRED,
				'Short message to be sent to the user (max. 255 characters)'
			)
			->addOption(
				'long-message',
				'l',
				InputOption::VALUE_REQUIRED,
				'Long mesage to be sent to the user (max. 4000 characters)',
				''
			)
			->addOption(
				'dummy',
				'd',
				InputOption::VALUE_NONE,
				'Create a full-flexed dummy notification for client debugging with actions and parameters (short-message will be casted to integer and is the number of actions (max 3))'
			)
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$userId = $input->getArgument('user-id');
		$subject = $input->getArgument('short-message');
		$message = $input->getOption('long-message');
		$dummy = $input->getOption('dummy');

		$user = $this->userManager->get($userId);
		if (!$user instanceof IUser) {
			$output->writeln('Unknown user');
			return 1;
		}

		if (!$dummy) {
			if ($subject === '' || strlen($subject) > 255) {
				$output->writeln('Too long or empty short-message');
				return 1;
			}

			if ($message !== '' && strlen($message) > 4000) {
				$output->writeln('Too long long-message');
				return 1;
			}

			$subjectTitle = 'cli';
		} else {
			$subject = (int) $subject;
			$subjectTitle = 'dummy';
		}

		$notification = $this->notificationManager->createNotification();
		$datetime = $this->timeFactory->getDateTime();

		try {
			$notification->setApp('admin_notifications')
				->setUser($user->getUID())
				->setDateTime($datetime)
				->setObject('admin_notifications', dechex($datetime->getTimestamp()))
				->setSubject($subjectTitle, [$subject]);

			if ($message !== '') {
				$notification->setMessage('cli', [$message]);
			}

			$this->notificationManager->notify($notification);
		} catch (\InvalidArgumentException $e) {
			$output->writeln('Error while sending the notification');
			return 1;
		}

		return 0;
	}
}
