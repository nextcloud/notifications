<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Settings;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Settings\ISettings;
use OCP\Util;

class Personal implements ISettings {
	public function __construct(
		protected IConfig $config,
		protected IAppConfig $appConfig,
		protected IL10N $l10n,
		protected IUserSession $session,
		protected SettingsMapper $settingsMapper,
		protected IInitialState $initialState,
	) {
	}

	#[\Override]
	public function getForm(): TemplateResponse {
		Util::addStyle('notifications', 'notifications-settings');
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
		} catch (DoesNotExistException) {
			$settings = new Settings();
			$settings->setUserId($user->getUID());
			$settings->setBatchTime(3600 * 3);
			$settings->setNextSendTime(1);
			$this->settingsMapper->insert($settings);

			$settingBatchTime = Settings::EMAIL_SEND_3HOURLY;
		}

		$defaultSoundNotification = $this->appConfig->getAppValueString(Application::APP_ID, 'sound_notification') === 'yes' ? 'yes' : 'no';
		$userSoundNotification = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'sound_notification', $defaultSoundNotification) === 'yes';
		$defaultSoundTalk = $this->appConfig->getAppValueString(Application::APP_ID, 'sound_talk') === 'yes' ? 'yes' : 'no';
		$userSoundTalk = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'sound_talk', $defaultSoundTalk) === 'yes';

		$this->initialState->provideInitialState('config', [
			'setting' => 'personal',
			'is_email_set' => (bool)$user->getEMailAddress(),
			'setting_batchtime' => $settingBatchTime,
			'sound_notification' => $userSoundNotification,
			'sound_talk' => $userSoundTalk,
		]);

		return new TemplateResponse('notifications', 'settings/personal');
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
