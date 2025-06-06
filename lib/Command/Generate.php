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
use OCP\RichObjectStrings\IValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Command {
	public function __construct(
		protected ITimeFactory $timeFactory,
		protected IUserManager $userManager,
		protected IManager $notificationManager,
		protected IValidator $richValidator,
		protected App $notificationApp,
	) {
		parent::__construct();
	}

	#[\Override]
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
				'short-parameters',
				null,
				InputOption::VALUE_REQUIRED,
				'JSON encoded array of Rich objects to fill the short-message, see https://github.com/nextcloud/server/blob/master/lib/public/RichObjectStrings/Definitions.php for more information',
			)
			->addOption(
				'long-message',
				'l',
				InputOption::VALUE_REQUIRED,
				'Long message to be sent to the user (max. 4000 characters)',
				''
			)
			->addOption(
				'long-parameters',
				null,
				InputOption::VALUE_REQUIRED,
				'JSON encoded array of Rich objects to fill the long-message, see https://github.com/nextcloud/server/blob/master/lib/public/RichObjectStrings/Definitions.php for more information',
			)
			->addOption(
				'object-type',
				null,
				InputOption::VALUE_REQUIRED,
				'If an object type and id is provided, previous notifications with the same type and id will be deleted for this user (max. 64 characters)',
			)
			->addOption(
				'object-id',
				null,
				InputOption::VALUE_REQUIRED,
				'If an object type and id is provided, previous notifications with the same type and id will be deleted for this user (max. 64 characters)',
			)
			->addOption(
				'dummy',
				'd',
				InputOption::VALUE_NONE,
				'Create a full-flexed dummy notification for client debugging with actions and parameters (short-message will be casted to integer and is the number of actions (max 3))'
			)
			->addOption(
				'output-id-only',
				null,
				InputOption::VALUE_NONE,
				'When specified only the notification ID that was generated will be printed in case of success'
			)
		;
	}

	#[\Override]
	protected function execute(InputInterface $input, OutputInterface $output): int {

		$userId = (string)$input->getArgument('user-id');
		$subject = (string)$input->getArgument('short-message');
		$subjectParametersString = (string)$input->getOption('short-parameters');
		$message = (string)$input->getOption('long-message');
		$messageParametersString = (string)$input->getOption('long-parameters');
		$dummy = $input->getOption('dummy');
		$idOnly = $input->getOption('output-id-only');
		$objectType = $input->getOption('object-type');
		$objectId = $input->getOption('object-id');

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

			if ($subjectParametersString !== '') {
				$subjectParameters = json_decode($subjectParametersString, true);
				if (!is_array($subjectParameters)) {
					$output->writeln('Short message parameters is not a valid json array');
					return 1;
				}
				$this->richValidator->validate($subject, $subjectParameters);
				$storeSubjectParameters = ['subject' => $subject, 'parameters' => $subjectParameters];
			} else {
				$storeSubjectParameters = [$subject];
			}

			if ($message !== '' && $messageParametersString !== '') {
				$messageParameters = json_decode($messageParametersString, true);
				if (!is_array($messageParameters)) {
					$output->writeln('Long message parameters is not a valid json array');
					return 1;
				}
				$this->richValidator->validate($message, $messageParameters);
				$storeMessageParameters = ['message' => $message, 'parameters' => $messageParameters];
			} else {
				$storeMessageParameters = [$message];
			}

			$subjectTitle = 'cli';
		} else {
			$storeSubjectParameters = [(int)$subject];
			$storeMessageParameters = [];
			$subjectTitle = 'dummy';
		}

		$notification = $this->notificationManager->createNotification();
		$datetime = $this->timeFactory->getDateTime();

		$isCustomObject = $objectId !== null && $objectType !== null;
		if (!$isCustomObject) {
			$objectId = dechex($datetime->getTimestamp());
			$objectType = 'admin_notifications';
		}

		try {
			$notification->setApp('admin_notifications')
				->setUser($user->getUID())
				->setObject($objectType, $objectId);

			if ($isCustomObject) {
				$this->notificationManager->markProcessed($notification);
				if (!$idOnly) {
					$output->writeln('<comment>Previous notification for ' . $objectType . '/' . $objectId . ' marked as processed</comment>');
				}
			}

			$notification->setDateTime($datetime)
				->setSubject($subjectTitle, $storeSubjectParameters);

			if ($message !== '') {
				$notification->setMessage('cli', $storeMessageParameters);
			}

			$this->notificationManager->notify($notification);
		} catch (\InvalidArgumentException) {
			$output->writeln('Error while sending the notification');
			return 1;
		}

		if ($idOnly) {
			$output->writeln((string)$this->notificationApp->getLastInsertedId());
		} else {
			$output->writeln('<info>Notification with ID ' . (string)($this->notificationApp->getLastInsertedId() ?? 0) . '</info>');
		}
		return 0;
	}
}
