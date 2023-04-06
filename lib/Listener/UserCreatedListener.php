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

namespace OCA\Notifications\Listener;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\User\Events\UserCreatedEvent;
use OCP\EventDispatcher\IEventListener;
use OCP\EventDispatcher\Event;
use OCP\IConfig;

/**
 * @template-implements IEventListener<Event|UserCreatedEvent>
 */
class UserCreatedListener implements IEventListener {
	private SettingsMapper $settingsMapper;
	private IConfig $config;


	public function __construct(SettingsMapper $settingsMapper, IConfig $config) {
		$this->settingsMapper = $settingsMapper;
		$this->config = $config;
	}

	public function handle(Event $event): void {
		if (!($event instanceof UserCreatedEvent)) {
			// Unrelated
			return;
		}

		$userId = $event->getUser()->getUID();

		$defaultSoundNotification = $this->config->getAppValue(Application::APP_ID, 'sound_notification') === 'yes' ? 'yes' : 'no';
		$defaultSoundTalk = $this->config->getAppValue(Application::APP_ID, 'sound_talk') === 'yes' ? 'yes' : 'no';
		$defaultBatchtime = (int) $this->config->getAppValue(Application::APP_ID, 'setting_batchtime');

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
