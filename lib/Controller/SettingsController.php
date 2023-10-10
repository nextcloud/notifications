<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021, Julien Barnoin <julien@barnoin.com>
 *
 * @author Julien Barnoin <julien@barnoin.com>
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

namespace OCA\Notifications\Controller;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends OCSController {
	protected IConfig $config;
	protected SettingsMapper $settingsMapper;
	protected string $userId;

	public function __construct(string $appName,
		IRequest $request,
		IConfig $config,
		SettingsMapper $settingsMapper,
		string $userId) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->settingsMapper = $settingsMapper;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 *
	 * Update personal notification settings
	 *
	 * @param int $batchSetting How often E-mails about missed notifications should be sent (hourly: 1; every three hours: 2; daily: 3; weekly: 4)
	 * @param string $soundNotification Enable sound for notifications ('yes' or 'no')
	 * @param string $soundTalk Enable sound for Talk notifications ('yes' or 'no')
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array{}>
	 *
	 * 200: Personal settings updated
	 */
	public function personal(int $batchSetting, string $soundNotification, string $soundTalk): DataResponse {
		$this->settingsMapper->setBatchSettingForUser($this->userId, $batchSetting);

		$this->config->setUserValue($this->userId, Application::APP_ID, 'sound_notification', $soundNotification !== 'no' ? 'yes' : 'no');
		$this->config->setUserValue($this->userId, Application::APP_ID, 'sound_talk', $soundTalk !== 'no' ? 'yes' : 'no');

		return new DataResponse();
	}

	/**
	 * @AuthorizedAdminSetting(settings=OCA\Notifications\Settings\Admin)
	 *
	 * Update default notification settings for new users
	 *
	 * @param int $batchSetting How often E-mails about missed notifications should be sent (hourly: 1; every three hours: 2; daily: 3; weekly: 4)
	 * @param string $soundNotification Enable sound for notifications ('yes' or 'no')
	 * @param string $soundTalk Enable sound for Talk notifications ('yes' or 'no')
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array{}>
	 *
	 * 200: Admin settings updated
	 */
	public function admin(int $batchSetting, string $soundNotification, string $soundTalk): DataResponse {
		$this->config->setAppValue(Application::APP_ID, 'setting_batchtime', (string) $batchSetting);
		$this->config->setAppValue(Application::APP_ID, 'sound_notification', $soundNotification !== 'no' ? 'yes' : 'no');
		$this->config->setAppValue(Application::APP_ID, 'sound_talk', $soundTalk !== 'no' ? 'yes' : 'no');

		return new DataResponse();
	}
}
