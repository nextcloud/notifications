<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\BackgroundJob;

use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IDBConnection;
use OCP\IUser;
use OCP\IUserManager;

class GenerateUserSettings extends TimedJob {
	public function __construct(
		ITimeFactory $time,
		private IDBConnection $connection,
		private IUserManager $userManager,
		private SettingsMapper $settingsMapper,
	) {
		parent::__construct($time);

		// run every day
		$this->setInterval(24 * 60 * 60);
	}

	#[\Override]
	protected function run($argument): void {
		$query = $this->connection->getQueryBuilder();
		$query->select('notification_id')
			->from('notifications')
			->orderBy('notification_id', 'DESC')
			->setMaxResults(1);

		$result = $query->executeQuery();
		$maxId = (int)$result->fetchOne();
		$result->closeCursor();

		$this->userManager->callForSeenUsers(function (IUser $user) use ($maxId): void {
			if ($user->isEnabled()) {
				return;
			}

			// Initializes the default settings
			$settings = $this->settingsMapper->getSettingsByUser($user->getUID());
			if ($settings->getLastSendId() === 0) {
				$settings->setLastSendId($maxId);
				$this->settingsMapper->update($settings);
			}
		});
	}
}
