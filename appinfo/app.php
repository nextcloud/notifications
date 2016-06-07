<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 * @author Tom Needham <tom@owncloud.com>
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

use OCA\Notifications\App;
use OCA\Notifications\Handler;
use OCP\Util;

\OC::$server->getNotificationManager()->registerApp(function() {
	return new App(
		new Handler(
			\OC::$server->getDatabaseConnection(),
			\OC::$server->getNotificationManager()
		)
	);
});

// Only display the app on index.php except for public shares
$request = \OC::$server->getRequest();
if (\OC::$server->getUserSession()->getUser() !== null
	&& substr($request->getScriptName(), 0 - strlen('/index.php')) === '/index.php'
	&& substr($request->getPathInfo(), 0, strlen('/s/')) !== '/s/'
	&& substr($request->getPathInfo(), 0, strlen('/login/')) !== '/login/') {
	Util::addScript('notifications', 'app');
	Util::addScript('notifications', 'notification');
	Util::addStyle('notifications', 'styles');
}
