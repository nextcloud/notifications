<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications;

use OCP\Capabilities\ICapability;

/**
 * Class Capabilities
 *
 * @package OCA\Notifications
 */
class Capabilities implements ICapability {
	/**
	 * Return this classes capabilities
	 *
	 * @return array{
	 *     notifications: array{
	 *         ocs-endpoints: string[],
	 *         push: string[],
	 *         admin-notifications: string[],
	 *     },
	 * }
	 */
	public function getCapabilities(): array {
		return [
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
	}
}
