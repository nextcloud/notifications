<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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

	/**
	 * @inheritDoc
	 */
	public function getCategory(): string {
		return 'notifications';
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return $this->l->t('Push notifications - Fair use policy');
	}

	/**
	 * @inheritDoc
	 */
	public function run(): SetupResult {
		$lastReached = (int) $this->config->getAppValue(Application::APP_ID, 'rate_limit_reached', '0');
		if ($lastReached < ($this->timeFactory->getTime() - 7 * 24 * 3600)) {
			return SetupResult::success();
		}

		return SetupResult::error(
			$this->l->t('Nextcloud GmbH sponsors a free push notification gateway for private users. To ensure good service, the gateway limits the number of push notifications per server and the limit was reached for this server. For enterprise users, a more scalable gateway is available.'),
			'https://nextcloud.com/fairusepolicy'
		);
	}
}
