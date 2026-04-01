<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications;

use OCP\AppFramework\Services\IAppConfig;
use OCP\Capabilities\ICapability;

/**
 * Class Capabilities
 *
 * @package OCA\Notifications
 */
class Capabilities implements ICapability {
	public function __construct(
		protected IAppConfig $appConfig,
	) {
	}

	/**
	 * Return this classes capabilities
	 *
	 * @return array{
	 *     notifications: array{
	 *         ocs-endpoints: list<string>,
	 *         push: list<string>,
	 *         admin-notifications: list<string>,
	 *     },
	 * }
	 */
	#[\Override]
	public function getCapabilities(): array {
		$capabilities = [
			'notifications' => [
				'ocs-endpoints' => [
					'list',
					'get',
					'delete',
					'delete-all',
					'icons',
					'rich-strings',
					'action-web',
					'user-status',
					'exists',
					'test-push',
				],
				'push' => [
					'devices',
					'object-data',
					'delete',
				],
				'admin-notifications' => [
					'ocs',
					'cli',
				],
			],
		];

		if ($this->appConfig->getAppValueBool('webpush_enabled')) {
			$capabilities['notifications']['push'][] = 'webpush';
			if ($this->appConfig->getAppValueBool('webpush_browsers_enabled')) {
				$capabilities['notifications']['push'][] = 'webpush-browsers';
			}
		}

		return $capabilities;
	}
}
