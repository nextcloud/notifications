<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
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

if (\OC::$server->getConfig()->getAppValue('notifications', 'debug')) {
	$controller = new \OCA\NotificationsIntegrationTesting\Controller(
		'notifications',
		\OC::$server->getRequest(),
		\OC::$server->getConfig(),
		\OC::$server->getNotificationManager()
	);
	\OCP\API::register(
		'post',
		'/apps/notifications/testing/notifiers',
		[$controller, 'fillNotifiers'],
		'notifications'
	);
	\OCP\API::register(
		'delete',
		'/apps/notifications/testing/notifiers',
		[$controller, 'clearNotifiers'],
		'notifications'
	);
	\OCP\API::register(
		'delete',
		'/apps/notifications/testing',
		[$controller, 'reset'],
		'notifications'
	);
	\OCP\API::register(
		'post',
		'/apps/notifications/testing/notifications',
		[$controller, 'addNotification'],
		'notifications'
	);
}
