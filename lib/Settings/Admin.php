<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Settings;

use OCA\Notifications\Model\Settings;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Services\IInitialState;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings {
	public function __construct(
		protected IAppConfig $appConfig,
		protected IInitialState $initialState,
	) {
	}

	#[\Override]
	public function getForm(): TemplateResponse {
		Util::addStyle('notifications', 'notifications-admin-settings');
		Util::addScript('notifications', 'notifications-admin-settings');

		$defaultSoundNotification = $this->appConfig->getAppValueBool('sound_notification');
		$defaultSoundTalk = $this->appConfig->getAppValueBool('sound_talk');
		$defaultBatchtime = $this->appConfig->getAppValueInt('setting_batchtime');
		$webpushEnabled = $this->appConfig->getAppValueBool('webpush_enabled');
		$webpushBrowsersEnabled = $this->appConfig->getAppValueBool('webpush_browsers_enabled');

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
			'sound_notification' => $defaultSoundNotification,
			'sound_talk' => $defaultSoundTalk,
			'webpush_enabled' => $webpushEnabled,
			'webpush_browsers_enabled' => $webpushBrowsersEnabled,
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
