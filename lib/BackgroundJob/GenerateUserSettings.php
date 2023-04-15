<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021, Joas Schilling <coding@schilljs.com>
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

namespace OCA\Notifications\BackgroundJob;

use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IDBConnection;
use OCP\IUser;
use OCP\IUserManager;

class GenerateUserSettings extends TimedJob {
	/** @var IDBConnection */
	private $connection;
	/** @var IUserManager */
	private $userManager;
	/** @var SettingsMapper */
	private $settingsMapper;

	public function __construct(
		ITimeFactory $time,
		IDBConnection $connection,
		IUserManager $userManager,
		SettingsMapper $settingsMapper
	) {
		parent::__construct($time);

		$this->connection = $connection;
		$this->userManager = $userManager;
		$this->settingsMapper = $settingsMapper;

		// run every day
		$this->setInterval(24 * 60 * 60);
	}

	protected function run($argument): void {
		$query = $this->connection->getQueryBuilder();
		$query->select('notification_id')
			->from('notifications')
			->orderBy('notification_id', 'DESC')
			->setMaxResults(1);

		$result = $query->executeQuery();
		$maxId = (int) $result->fetchOne();
		$result->closeCursor();

		$this->userManager->callForSeenUsers(function (IUser $user) use ($maxId) {
			if ($user->isEnabled()) {
				return;
			}

			try {
				$this->settingsMapper->getSettingsByUser($user->getUID());
			} catch (DoesNotExistException $e) {
				$settings = new Settings();
				$settings->setUserId($user->getUID());
				$settings->setNextSendTime(1);
				$settings->setBatchTime(Settings::EMAIL_SEND_3HOURLY);
				$settings->setLastSendId($maxId);
				$this->settingsMapper->insert($settings);
			}
		});
	}
}
