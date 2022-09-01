<?php

declare(strict_types=1);

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

class Admin implements ISettings
{
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

    public function __construct(IConfig        $config,
                                IL10N          $l10n,
                                IUserSession   $session,
                                SettingsMapper $settingsMapper,
                                IInitialState  $initialState)
    {
        $this->config = $config;
        $this->l10n = $l10n;
        $this->settingsMapper = $settingsMapper;
        $this->session = $session;
        $this->initialState = $initialState;
    }

    /**
     * @return TemplateResponse
     */
    public function getForm(): TemplateResponse
    {
        Util::addScript('notifications', 'notifications-adminSettings');

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

        $this->initialState->provideInitialState('config', [
            'setting' => 'admin',
            'setting_batchtime' => $default_batchtime,
            'sound_notification' => $default_sound_notification === 'yes',
            'sound_talk' => $default_sound_talk === 'yes',
        ]);

        return new TemplateResponse('notifications', 'settings/admin');
    }

    /**
     * @return string the section ID, e.g. 'sharing'
     */
    public function getSection(): string
    {
        return 'notifications';
    }

    /**
     * @return int whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     *
     * E.g.: 70
     */
    public function getPriority(): int
    {
        return 20;
    }
}