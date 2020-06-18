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
use OCA\Notifications\Handler;
use OCA\Notifications\Notifier\AdminNotifications;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\IAppContainer;
use OCP\Util;

class Application extends \OCP\AppFramework\App implements IBootstrap {

	public const APP_ID = 'notifications';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerCapability(Capabilities::class);

		$context->registerService(IProvider::class, function(IAppContainer $c) {
			return $c->getServer()->query(IProvider::class);
		});
	}

	public function boot(IBootContext $context): void {
		// notification app
		$context->getServerContainer()
			->getNotificationManager()
			->registerApp(App::class);

		// admin notifications
		$this->getContainer()
			->getServer()
			->getNotificationManager()
			->registerNotifierService(AdminNotifications::class);

		// User interface
		$request = $context->getServerContainer()->getRequest();

		if ($context->getServerContainer()->getUserSession()->getUser() !== null
			&& strpos($request->getPathInfo(), '/s/') !== 0
			&& strpos($request->getPathInfo(), '/login/') !== 0
			&& substr($request->getScriptName(), 0 - \strlen('/index.php')) === '/index.php') {

			Util::addScript('notifications', 'notifications');
			Util::addStyle('notifications', 'styles');
		}

		// User delete hook
		Util::connectHook('OC_User', 'post_deleteUser', $this, 'deleteUser');
	}

	public function deleteUser(array $params): void {
		/** @var Handler $handler */
		$handler = $this->getContainer()->query(Handler::class);
		$handler->deleteByUser($params['uid']);
	}
}
