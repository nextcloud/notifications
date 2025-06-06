<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Settings;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings {
	public function __construct(
		protected IConfig $config,
		protected IInitialState $initialState,
	) {
	}

	#[\Override]
	public function getForm(): TemplateResponse {
		Util::addStyle('notifications', 'notifications-admin-settings');
		Util::addScript('notifications', 'notifications-admin-settings');

		$defaultSoundNotification = $this->config->getAppValue(Application::APP_ID, 'sound_notification') === 'yes' ? 'yes' : 'no';
		$defaultSoundTalk = $this->config->getAppValue(Application::APP_ID, 'sound_talk') === 'yes' ? 'yes' : 'no';
		$defaultBatchtime = (int)$this->config->getAppValue(Application::APP_ID, 'setting_batchtime');

		if ($defaultBatchtime !== Settings::EMAIL_SEND_WEEKLY
			&& $defaultBatchtime !== Settings::EMAIL_SEND_DAILY
			&& $defaultBatchtime !== Settings::EMAIL_SEND_3HOURLY
			&& $defaultBatchtime !== Settings::EMAIL_SEND_HOURLY
			&& $defaultBatchtime !== Settings::EMAIL_SEND_OFF) {
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

	#[\Override]
	public function getSection(): string {
		return 'notifications';
	}

	#[\Override]
	public function getPriority(): int {
		return 20;
	}
}
