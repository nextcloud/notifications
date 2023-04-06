<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022, Nikita Toponen <natoponen@gmail.com>
 *
 * @author Nikita Toponen <natoponen@gmail.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Notifications\Settings;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;
use OCP\IUserSession;
use OCP\Util;

class Admin implements ISettings {
	protected IConfig $config;
	protected IL10N $l10n;
	private SettingsMapper $settingsMapper;
	private IUserSession $session;
	private IInitialState $initialState;

	public function __construct(IConfig        $config,
								IL10N          $l10n,
								IUserSession   $session,
								SettingsMapper $settingsMapper,
								IInitialState  $initialState) {
		$this->config = $config;
		$this->l10n = $l10n;
		$this->settingsMapper = $settingsMapper;
		$this->session = $session;
		$this->initialState = $initialState;
	}

	public function getForm(): TemplateResponse {
		Util::addScript('notifications', 'notifications-admin-settings');

		$defaultSoundNotification = $this->config->getAppValue(Application::APP_ID, 'sound_notification') === 'yes' ? 'yes' : 'no';
		$defaultSoundTalk = $this->config->getAppValue(Application::APP_ID, 'sound_talk') === 'yes' ? 'yes' : 'no';
		$defaultBatchtime = (int) $this->config->getAppValue(Application::APP_ID, 'setting_batchtime');

		if ($defaultBatchtime != Settings::EMAIL_SEND_WEEKLY
			&& $defaultBatchtime != Settings::EMAIL_SEND_DAILY
			&& $defaultBatchtime != Settings::EMAIL_SEND_3HOURLY
			&& $defaultBatchtime != Settings::EMAIL_SEND_HOURLY
			&& $defaultBatchtime != Settings::EMAIL_SEND_OFF) {
			$defaultBatchtime = Settings::EMAIL_SEND_3HOURLY;
		}

		$this->initialState->provideInitialState('config', [
			'setting' => 'admin',
			'setting_batchtime' => $defaultBatchtime,
			'sound_notification' => $defaultSoundNotification === 'yes',
			'sound_talk' => $defaultSoundTalk === 'yes',
		]);

		return new TemplateResponse('notifications', 'settings/admin');
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
