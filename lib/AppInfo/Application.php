<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications\AppInfo;

use OCA\Notifications\App;
use OCA\Notifications\Capabilities;
use OCA\Notifications\Listener\AddMissingIndicesListener;
use OCA\Notifications\Listener\BeforeTemplateRenderedListener;
use OCA\Notifications\Listener\PostLoginListener;
use OCA\Notifications\Listener\UserCreatedListener;
use OCA\Notifications\Listener\UserDeletedListener;
use OCA\Notifications\Notifier\AdminNotifications;
use OCA\Notifications\Settings\SetupWarningOnRateLimitReached;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\DB\Events\AddMissingIndicesEvent;
use OCP\Notification\IManager;
use OCP\User\Events\PostLoginEvent;
use OCP\User\Events\UserCreatedEvent;
use OCP\User\Events\UserDeletedEvent;

class Application extends \OCP\AppFramework\App implements IBootstrap {
	public const APP_ID = 'notifications';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerCapability(Capabilities::class);

		$context->registerSetupCheck(SetupWarningOnRateLimitReached::class);

		$context->registerNotifierService(AdminNotifications::class);

		$context->registerEventListener(AddMissingIndicesEvent::class, AddMissingIndicesListener::class);
		$context->registerEventListener(UserDeletedEvent::class, UserDeletedListener::class);
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
		$context->registerEventListener(UserCreatedEvent::class, UserCreatedListener::class);
		$context->registerEventListener(PostLoginEvent::class, PostLoginListener::class);
	}

	public function boot(IBootContext $context): void {
		$context->injectFn(\Closure::fromCallable([$this, 'registerAppAndNotifier']));
	}

	public function registerAppAndNotifier(IManager $notificationManager): void {
		// notification app
		$notificationManager->registerApp(App::class);
	}
}
