<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Settings;

use OCA\Notifications\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\IL10N;
use OCP\SetupCheck\ISetupCheck;
use OCP\SetupCheck\SetupResult;

class SetupWarningOnRateLimitReached implements ISetupCheck {
	public function __construct(
		private IConfig $config,
		private ITimeFactory $timeFactory,
		private IL10N $l,
	) {
	}

	#[\Override]
	public function getCategory(): string {
		return 'notifications';
	}

	#[\Override]
	public function getName(): string {
		return $this->l->t('Push notifications - Fair use policy');
	}

	#[\Override]
	public function run(): SetupResult {
		$lastReached = (int)$this->config->getAppValue(Application::APP_ID, 'rate_limit_reached', '0');
		if ($lastReached < ($this->timeFactory->getTime() - 7 * 24 * 3600)) {
			return SetupResult::success();
		}

		return SetupResult::error(
			$this->l->t('Nextcloud GmbH sponsors a free push notification gateway for private users. To ensure good service, the gateway limits the number of push notifications per server and the limit was reached for this server. For enterprise users, a more scalable gateway is available.'),
			'https://nextcloud.com/fairusepolicy'
		);
	}
}
