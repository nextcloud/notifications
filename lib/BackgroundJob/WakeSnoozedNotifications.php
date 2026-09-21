<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\BackgroundJob;

use OCA\Notifications\Service\SnoozeService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class WakeSnoozedNotifications extends TimedJob {
	public const BATCH_SIZE = 1000;

	public function __construct(
		ITimeFactory $time,
		private readonly SnoozeService $snoozeService,
	) {
		parent::__construct($time);

		// run every 5 minutes
		$this->setInterval(5 * 60);
	}

	#[\Override]
	protected function run($argument): void {
		$this->snoozeService->wakeUpDue($this->time->getTime(), self::BATCH_SIZE);
	}
}
