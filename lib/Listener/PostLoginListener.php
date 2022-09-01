<?php

namespace OCA\Notifications\Listener;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\User\Events\PostLoginEvent;
use OCP\EventDispatcher\IEventListener;
use OCP\EventDispatcher\Event;
use OCP\IUserManager;
use OCP\IConfig;

class PostLoginListener implements IEventListener {
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
        if (!($event instanceof PostLoginEvent)) {
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

        try {
            $this->settingsMapper->getSettingsByUser($userId);
        } catch (DoesNotExistException $e) {
            $this->config->setUserValue($userId, Application::APP_ID, 'sound_notification', $default_sound_notification);
            $this->config->setUserValue($userId, Application::APP_ID, 'sound_talk', $default_sound_talk);
            $this->settingsMapper->setBatchSettingForUser($userId, $default_batchtime);
        }
    }
}