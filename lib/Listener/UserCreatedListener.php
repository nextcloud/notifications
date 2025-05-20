<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Listener;

use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\User\Events\UserCreatedEvent;

/**
 * @template-implements IEventListener<Event|UserCreatedEvent>
 */
class UserCreatedListener implements IEventListener {
	public function __construct(
		private SettingsMapper $settingsMapper,
		private IConfig $config,
	) {
	}

	#[\Override]
	public function handle(Event $event): void {
		if (!($event instanceof UserCreatedEvent)) {
			// Unrelated
			return;
		}

		$userId = $event->getUser()->getUID();

		// Initializes the default settings
		$this->settingsMapper->getSettingsByUser($userId);
	}
}
