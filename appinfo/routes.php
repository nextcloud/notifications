<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

return [
	'ocs' => [
		['name' => 'Endpoint#listNotifications', 'url' => '/api/{apiVersion}/notifications', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1|v2)']],
		['name' => 'Endpoint#getNotification', 'url' => '/api/{apiVersion}/notifications/{id}', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1|v2)', 'id' => '\d+']],
		['name' => 'Endpoint#confirmIdsForUser', 'url' => '/api/{apiVersion}/notifications/exists', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1|v2)']],
		['name' => 'Endpoint#deleteNotification', 'url' => '/api/{apiVersion}/notifications/{id}', 'verb' => 'DELETE', 'requirements' => ['apiVersion' => '(v1|v2)', 'id' => '\d+']],
		['name' => 'Endpoint#deleteAllNotifications', 'url' => '/api/{apiVersion}/notifications', 'verb' => 'DELETE', 'requirements' => ['apiVersion' => '(v1|v2)']],

		['name' => 'API#generateNotification', 'url' => '/api/{apiVersion}/admin_notifications/{userId}', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1|v2)']],
		['name' => 'API#generateNotificationV3', 'url' => '/api/{apiVersion3}/admin_notifications/{userId}', 'verb' => 'POST', 'requirements' => ['apiVersion3' => '(v3)']],
		['name' => 'API#selfTestPush', 'url' => '/api/{apiVersion3}/test/self', 'verb' => 'POST', 'requirements' => ['apiVersion3' => '(v3)']],

		['name' => 'Settings#personal', 'url' => '/api/{apiVersion}/settings', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v2)']],
		['name' => 'Settings#admin', 'url' => '/api/{apiVersion}/settings/admin', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v2)']],
	],
];
