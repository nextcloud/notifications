<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
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
		['name' => 'Endpoint#deleteNotification', 'url' => '/api/{apiVersion}/notifications/{id}', 'verb' => 'DELETE', 'requirements' => ['apiVersion' => 'v(1|2)', 'id' => '\d+']],
		['name' => 'Push#registerDevice', 'url' => '/api/v3/push', 'verb' => 'POST'],
		['name' => 'Push#removeDevice', 'url' => '/api/v3/push', 'verb' => 'DELETE'],
	],
];
