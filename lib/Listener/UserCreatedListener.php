<?php

/**
 * @copyright Copyright (c) 2022 Nikita Toponen <natoponen@gmail.com>
 *
 * @author Nikita Toponen <natoponen@gmail.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Notifications\Listener;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\IUserManager;
use OCP\User\Events\UserCreatedEvent;
use OCP\EventDispatcher\IEventListener;
use OCP\EventDispatcher\Event;
use OCP\IConfig;

class UserCreatedListener implements IEventListener {
    /** @var IUserManager */
    private $userManager;
    /** @var SettingsMapper */
    private $settingsMapper;
    /** @var IConfig */
    private $config;


    public function __construct(IUserManager $userManager, SettingsMapper $settingsMapper, IConfig $config) {
        $this->userManager = $userManager;
        $this->settingsMapper = $settingsMapper;
        $this->config = $config;
    }

    public function handle(Event $event): void {
        if (!($event instanceof UserCreatedEvent)) {
            // Unrelated
            return;
        }

        $userId = $event->getUser()->getUID();

        $default_sound_notification = $this->config->getAppValue(Application::APP_ID, 'sound_notification') === 'yes' ? 'yes' : 'no';
        $default_sound_talk = $this->config->getAppValue(Application::APP_ID, 'sound_talk') === 'yes' ? 'yes' : 'no';
        $default_batchtime = $this->config->getAppValue(Application::APP_ID, 'setting_batchtime');

        if ($default_batchtime != Settings::EMAIL_SEND_WEEKLY
            && $default_batchtime != Settings::EMAIL_SEND_DAILY
            && $default_batchtime != Settings::EMAIL_SEND_3HOURLY
            && $default_batchtime != Settings::EMAIL_SEND_HOURLY
            && $default_batchtime != Settings::EMAIL_SEND_OFF) {
            $default_batchtime = Settings::EMAIL_SEND_3HOURLY;
        }

        $this->config->setUserValue($userId, Application::APP_ID, 'sound_notification', $default_sound_notification);
        $this->config->setUserValue($userId, Application::APP_ID, 'sound_talk', $default_sound_talk);
        $this->settingsMapper->setBatchSettingForUser($userId, $default_batchtime);
    }
}
