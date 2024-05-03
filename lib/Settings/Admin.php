<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Settings;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IUserSession;
use OCP\Settings\ISettings;
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
