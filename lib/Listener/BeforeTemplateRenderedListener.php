<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021, Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
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
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\Http\TemplateResponse;
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
	protected IConfig $config;
	protected IUserSession $userSession;
	protected IInitialState $initialState;
	protected IManager $notificationManager;

	public function __construct(IConfig $config,
		IUserSession $userSession,
		IInitialState $initialState,
		IManager $notificationManager) {
		$this->config = $config;
		$this->userSession = $userSession;
		$this->initialState = $initialState;
		$this->notificationManager = $notificationManager;
	}

	public function handle(Event $event): void {
		if (!($event instanceof BeforeTemplateRenderedEvent)) {
			// Unrelated
			return;
		}

		if ($event->getResponse()->getRenderAs() !== TemplateResponse::RENDER_AS_USER) {
			return;
		}

		if (!$this->userSession->getUser() instanceof IUser) {
			return;
		}

		$this->initialState->provideInitialState(
			'sound_notification',
			$this->config->getUserValue(
				$this->userSession->getUser()->getUID(),
				Application::APP_ID,
				'sound_notification',
				'yes'
			) === 'yes'
		);

		$this->initialState->provideInitialState(
			'sound_talk',
			$this->config->getUserValue(
				$this->userSession->getUser()->getUID(),
				Application::APP_ID,
				'sound_talk',
				'yes'
			) === 'yes'
		);

		/**
		 * We want to keep offering our push notification service for free, but large
		 * users overload our infrastructure. For this reason we have to rate-limit the
		 * use of push notifications. If you need this feature, consider using Nextcloud Enterprise.
		 */
		$this->initialState->provideInitialState(
			'throttled_push_notifications',
			!$this->notificationManager->isFairUseOfFreePushService()
		);

		Util::addScript('notifications', 'notifications-main');
	}
}
