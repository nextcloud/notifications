<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Julien Barnoin <julien@barnoin.com>
 *
 * @license GNU AGPL version 3 or any later version
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

namespace OCA\Notifications\Controller;

use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;

class SettingsController extends OCSController {
	/** @var SettingsMapper */
	protected $settingsMapper;

	/** @var ITimeFactory */
	protected $timeFactory;

	/** @var string */
	protected $userId;

	public function __construct(string $appName,
								IRequest $request,
								SettingsMapper $settingsMapper,
								ITimeFactory $timeFactory,
								string $userId) {
		parent::__construct($appName, $request);
		$this->settingsMapper = $settingsMapper;
		$this->timeFactory = $timeFactory;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $batchSetting
	 * @return DataResponse
	 */
	public function personal(int $batchSetting): DataResponse {
		try {
			$settings = $this->settingsMapper->getSettingsByUser($this->userId);
		} catch (DoesNotExistException $e) {
			$settings = new Settings();
			$settings->setUserId($this->userId);
			/** @var Settings $settings */
			$settings = $this->settingsMapper->insert($settings);
		}

		$batchTime = 0; // Off
		if ($batchSetting === Settings::EMAIL_SEND_WEEKLY) {
			$batchTime = 3600 * 24 * 7;
		} elseif ($batchSetting === Settings::EMAIL_SEND_DAILY) {
			$batchTime = 3600 * 24;
		} elseif ($batchSetting === Settings::EMAIL_SEND_3HOURLY) {
			$batchTime = 3600 * 3;
		} elseif ($batchSetting === Settings::EMAIL_SEND_HOURLY) {
			$batchTime = 3600;
		}

		$settings->setBatchTime($batchTime);
		if ($batchTime === 0) {
			$settings->setNextSendTime(0);
		} else {
			// This will automatically heal on the first run of the background job.
			// We are just setting it to 1, so it's checked soon in case
			// the time is now shorter and should trigger already.
			$settings->setNextSendTime(1);
		}
		$this->settingsMapper->update($settings);

		return new DataResponse();
	}
}
