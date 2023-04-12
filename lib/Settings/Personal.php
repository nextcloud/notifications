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

namespace OCA\Notifications\Settings;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IUser;
use OCP\Settings\ISettings;
use OCP\IUserSession;
use OCP\Util;

class Personal implements ISettings {
	/** @var \OCP\IConfig */
	protected $config;

	/** @var \OCP\IL10N */
	protected $l10n;

	/** @var SettingsMapper */
	private $settingsMapper;

	/** @var IUserSession */
	private $session;

	/** @var IInitialState */
	private $initialState;

	public function __construct(IConfig $config,
								IL10N $l10n,
								IUserSession $session,
								SettingsMapper $settingsMapper,
								IInitialState $initialState) {
		$this->config = $config;
		$this->l10n = $l10n;
		$this->settingsMapper = $settingsMapper;
		$this->session = $session;
		$this->initialState = $initialState;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		Util::addScript('notifications', 'notifications-settings');

		/** @var IUser $user */
		$user = $this->session->getUser();
		try {
			$settings = $this->settingsMapper->getSettingsByUser($user->getUID());

			if ($settings->getBatchTime() === 3600 * 24 * 7) {
				$settingBatchTime = Settings::EMAIL_SEND_WEEKLY;
			} elseif ($settings->getBatchTime() === 3600 * 24) {
				$settingBatchTime = Settings::EMAIL_SEND_DAILY;
			} elseif ($settings->getBatchTime() === 3600 * 3) {
				$settingBatchTime = Settings::EMAIL_SEND_3HOURLY;
			} elseif ($settings->getBatchTime() === 3600) {
				$settingBatchTime = Settings::EMAIL_SEND_HOURLY;
			} else {
				$settingBatchTime = Settings::EMAIL_SEND_OFF;
			}
		} catch (DoesNotExistException $e) {
			$settings = new Settings();
			$settings->setUserId($user->getUID());
			$settings->setBatchTime(3600 * 3);
			$settings->setNextSendTime(1);
			$this->settingsMapper->insert($settings);

			$settingBatchTime = Settings::EMAIL_SEND_3HOURLY;
		}

		$this->initialState->provideInitialState('config', [
			'setting' => 'personal',
			'is_email_set' => (bool)$user->getEMailAddress(),
			'setting_batchtime' => $settingBatchTime,
			'sound_notification' => $this->config->getUserValue($user->getUID(), Application::APP_ID, 'sound_notification', 'yes') === 'yes',
			'sound_talk' => $this->config->getUserValue($user->getUID(), Application::APP_ID, 'sound_talk', 'yes') === 'yes',
		]);

		return new TemplateResponse('notifications', 'settings/personal');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection(): string {
		return 'notifications';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority(): int {
		return 20;
	}
}
