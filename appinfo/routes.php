<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023, Joas Schilling <coding@schilljs.com>
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

return [
	'ocs' => [
		['name' => 'Endpoint#listNotifications', 'url' => '/api/{apiVersion}/notifications', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v(1|2)']],
		['name' => 'Endpoint#getNotification', 'url' => '/api/{apiVersion}/notifications/{id}', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v(1|2)', 'id' => '\d+']],
		['name' => 'Endpoint#confirmIdsForUser', 'url' => '/api/{apiVersion}/notifications/exists', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v(1|2)']],
		['name' => 'Endpoint#deleteNotification', 'url' => '/api/{apiVersion}/notifications/{id}', 'verb' => 'DELETE', 'requirements' => ['apiVersion' => 'v(1|2)', 'id' => '\d+']],
		['name' => 'Endpoint#deleteAllNotifications', 'url' => '/api/{apiVersion}/notifications', 'verb' => 'DELETE', 'requirements' => ['apiVersion' => 'v(1|2)']],
		['name' => 'Push#registerDevice', 'url' => '/api/{apiVersion}/push', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v2']],
		['name' => 'Push#removeDevice', 'url' => '/api/{apiVersion}/push', 'verb' => 'DELETE', 'requirements' => ['apiVersion' => 'v2']],

		['name' => 'API#generateNotification', 'url' => '/api/{apiVersion}/admin_notifications/{userId}', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v(1|2)']],

		['name' => 'Settings#personal', 'url' => '/api/{apiVersion}/settings', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v2']],
		['name' => 'Settings#admin', 'url' => '/api/{apiVersion}/settings/admin', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v2']],
	],
];
