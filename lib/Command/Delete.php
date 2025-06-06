<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Command;

use OCA\Notifications\Exceptions\NotificationNotFoundException;
use OCA\Notifications\Handler;
use OCP\Notification\IManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Delete extends Command {
	public function __construct(
		protected IManager $notificationManager,
		protected Handler $notificationHandler,
	) {
		parent::__construct();
	}

	#[\Override]
	protected function configure(): void {
		$this
			->setName('notification:delete')
			->setDescription('Delete a generated admin notification for the given user')
			->addArgument(
				'user-id',
				InputArgument::REQUIRED,
				'User ID of the user to notify'
			)
			->addArgument(
				'notification-id',
				InputArgument::REQUIRED,
				'The notification ID returned by the "notification:generate" command'
			)
		;
	}

	#[\Override]
	protected function execute(InputInterface $input, OutputInterface $output): int {

		$userId = (string)$input->getArgument('user-id');
		$notificationId = (int)$input->getArgument('notification-id');

		try {
			$notification = $this->notificationHandler->getById($notificationId, $userId);
		} catch (NotificationNotFoundException) {
			$output->writeln('<error>Notification not found for user</error>');
			return 1;
		}

		$this->notificationManager->markProcessed($notification);
		$output->writeln('<info>Notification deleted successfully</info>');
		return 0;
	}
}
