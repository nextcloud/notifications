<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\BackgroundJob;

use OCA\Notifications\MailNotifications;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class SendNotificationMails extends TimedJob {
	public function __construct(
		ITimeFactory $timeFactory,
		private MailNotifications $mailNotifications,
		private bool $isCLI,
	) {
		parent::__construct($timeFactory);
	}

	#[\Override]
	protected function run($argument): void {
		$time = $this->time->getTime();
		$batchSize = $this->isCLI ? MailNotifications::BATCH_SIZE_CLI : MailNotifications::BATCH_SIZE_WEB;
		$this->mailNotifications->sendEmails($batchSize, $time);
	}
}
