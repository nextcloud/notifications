<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Listener;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\User\Events\PostLoginEvent;

/**
 * @template-implements IEventListener<Event|PostLoginEvent>
 */
class PostLoginListener implements IEventListener {
	public function __construct(
		private SettingsMapper $settingsMapper,
		private IConfig $config,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof PostLoginEvent)) {
			// Unrelated
			return;
		}

		$userId = $event->getUser()->getUID();

		try {
			$this->settingsMapper->getSettingsByUser($userId);
		} catch (DoesNotExistException) {
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

			$this->config->setUserValue($userId, Application::APP_ID, 'sound_notification', $defaultSoundNotification);
			$this->config->setUserValue($userId, Application::APP_ID, 'sound_talk', $defaultSoundTalk);
			$this->settingsMapper->setBatchSettingForUser($userId, $defaultBatchtime);
		}
	}
}
