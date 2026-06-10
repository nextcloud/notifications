<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\BackgroundJob;

use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IDBConnection;
use OCP\IUser;
use OCP\IUserManager;

class GenerateUserSettings extends TimedJob {
	public function __construct(
		ITimeFactory $time,
		private readonly IDBConnection $connection,
		private readonly IUserManager $userManager,
		private readonly SettingsMapper $settingsMapper,
		private readonly IAppConfig $appConfig,
	) {
		parent::__construct($time);

		// run every day
		$this->setInterval(24 * 60 * 60);
	}

	#[\Override]
	protected function run($argument): void {
		$update = $this->connection->getQueryBuilder();
		$update->update('notifications_settings')
			->set('next_send_time', $update->createNamedParameter(1))
			->where($update->expr()->eq('next_send_time', $update->createNamedParameter(0)))
			->andWhere($update->expr()->neq('batch_time', $update->createNamedParameter(Settings::EMAIL_SEND_OFF)));

		$batchTime = $this->appConfig->getAppValueInt('setting_batchtime');
		if ($batchTime === Settings::EMAIL_SEND_OFF) {
			$update->andWhere($update->expr()->neq('batch_time', $update->createNamedParameter(Settings::EMAIL_SEND_DEFAULT)));
		}
		$update->executeStatement();

		$query = $this->connection->getQueryBuilder();
		$query->select('notification_id')
			->from('notifications')
			->orderBy('notification_id', 'DESC')
			->setMaxResults(1);

		$result = $query->executeQuery();
		$maxId = (int)$result->fetchOne();
		$result->closeCursor();

		$this->userManager->callForSeenUsers(function (IUser $user) use ($maxId): void {
			if (!$user->isEnabled()) {
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
