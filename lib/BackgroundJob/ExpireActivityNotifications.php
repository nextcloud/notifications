<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\BackgroundJob;

use OCA\Notifications\Handler;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\IJob;
use OCP\BackgroundJob\TimedJob;
use OCP\IConfig;

class ExpireActivityNotifications extends TimedJob {
	public function __construct(
		ITimeFactory $time,
		private readonly IConfig $config,
		private readonly Handler $handler,
	) {
		parent::__construct($time);

		// run every 30 minutes
		$this->setInterval(30 * 60);
		$this->setTimeSensitivity(IJob::TIME_INSENSITIVE);
	}

	#[\Override]
	protected function run($argument): void {
		// Mirrors the "activity" app's own ExpireActivities job/default, so we only
		// remove a notification once the activity it points to is guaranteed expired.
		$expireDays = $this->config->getSystemValueInt('activity_expire_days', 365);
		$olderThan = $this->time->getTime() - (60 * 60 * 24 * max(1, $expireDays));

		$this->handler->expireOlderThan('activity_notification', $olderThan, \OC::$CLI ? 1000 : 50);
	}
}
