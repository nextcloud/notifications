<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Listener;

use OCA\Notifications\AppInfo\Application;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IManager;
use OCP\Util;

/**
 * @template-implements IEventListener<Event|BeforeTemplateRenderedEvent>
 */
class BeforeTemplateRenderedListener implements IEventListener {
	public function __construct(
		protected IConfig $config,
		protected IUserSession $userSession,
		protected IInitialState $initialState,
		protected IManager $notificationManager,
		protected IAppConfig $appConfig,
	) {
	}

	#[\Override]
	public function handle(Event $event): void {
		if (!($event instanceof BeforeTemplateRenderedEvent)) {
			// Unrelated
			return;
		}

		if ($event->getResponse()->getRenderAs() !== TemplateResponse::RENDER_AS_USER) {
			return;
		}

		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return;
		}

		$defaultSoundNotification = $this->appConfig->getAppValueString(Application::APP_ID, 'sound_notification') === 'yes' ? 'yes' : 'no';
		$userSoundNotification = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'sound_notification', $defaultSoundNotification) === 'yes';
		$defaultSoundTalk = $this->appConfig->getAppValueString(Application::APP_ID, 'sound_talk') === 'yes' ? 'yes' : 'no';
		$userSoundTalk = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'sound_talk', $defaultSoundTalk) === 'yes';

		$this->initialState->provideInitialState('sound_notification', $userSoundNotification);

		$this->initialState->provideInitialState('sound_talk', $userSoundTalk);

		/**
		 * We want to keep offering our push notification service for free, but large
		 * users overload our infrastructure. For this reason we have to rate-limit the
		 * use of push notifications. If you need this feature, consider using Nextcloud Enterprise.
		 */
		$this->initialState->provideInitialState(
			'throttled_push_notifications',
			!$this->notificationManager->isFairUseOfFreePushService()
		);

		Util::addStyle('notifications', 'notifications-main');
		Util::addScript('notifications', 'notifications-main');
	}
}
