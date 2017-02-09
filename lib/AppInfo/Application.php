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

namespace OCA\Notifications\AppInfo;

use OC\Authentication\Token\IProvider;
use OCA\Notifications\App;
use OCA\Notifications\Capabilities;
use OCA\Notifications\Controller\EndpointController;
use OCP\AppFramework\IAppContainer;
use OCP\Util;

class Application extends \OCP\AppFramework\App {
	public function __construct() {
		parent::__construct('notifications');
		$container = $this->getContainer();

		$container->registerAlias('EndpointController', EndpointController::class);
		$container->registerCapability(Capabilities::class);

		// FIXME this is for automatic DI because it is not in DIContainer
		$container->registerService(IProvider::class, function(IAppContainer $c) {
			return $c->getServer()->query(IProvider::class);
		});
	}

	public function register() {
		$this->registerNotificationApp();
		$this->registerUserInterface();
	}

	protected function registerNotificationApp() {
		$container = $this->getContainer();
		$container->getServer()->getNotificationManager()->registerApp(function() use($container) {
			return $container->query(App::class);
		});
	}

	protected function registerUserInterface() {
		// Only display the app on index.php except for public shares
		$server = $this->getContainer()->getServer();
		$request = $server->getRequest();

		if ($server->getUserSession()->getUser() !== null
			&& substr($request->getScriptName(), 0 - strlen('/index.php')) === '/index.php'
			&& substr($request->getPathInfo(), 0, strlen('/s/')) !== '/s/'
			&& substr($request->getPathInfo(), 0, strlen('/login/')) !== '/login/') {
			Util::addScript('notifications', 'app');
			Util::addScript('notifications', 'notification');
			Util::addScript('notifications', 'richObjectStringParser');
			Util::addStyle('notifications', 'styles');
		}

	}
}
