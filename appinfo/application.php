<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
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

use OC\Files\View;
use OCA\Activity\Consumer;
use OCA\Activity\Controller\Activities;
use OCA\Activity\Controller\Feed;
use OCA\Activity\Controller\Settings;
use OCA\Activity\Data;
use OCA\Activity\DataHelper;
use OCA\Activity\GroupHelper;
use OCA\Activity\FilesHooks;
use OCA\Activity\MailQueueHandler;
use OCA\Activity\Navigation;
use OCA\Activity\ParameterHelper;
use OCA\Activity\UserSettings;
use OCA\AnnouncementCenter\Controller\PageController;
use OCA\AnnouncementCenter\Manager;
use OCA\Notifications\Capabilities;
use OCA\Notifications\Controller\EndPointController;
use OCA\Notifications\Handler;
use OCP\AppFramework\App;
use OCP\IContainer;
use OCP\IUser;
use OCP\IUserSession;

class Application extends App {
	public function __construct (array $urlParams = array()) {
		parent::__construct('notifications', $urlParams);
		$container = $this->getContainer();

		$container->registerService('EndpointController', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			return new EndpointController(
				$c->query('AppName'),
				$server->getRequest(),
				new Handler(
					$server->getDatabaseConnection(),
					$server->getNotificationManager()
				),
				$server->getNotificationManager(),
				$server->getConfig(),
				$this->getCurrentUser($server->getUserSession())
			);
		});

		$container->registerService('Capabilities', function(IContainer $c) {
			return new Capabilities();
		});

		$container->registerCapability('Capabilities');
	}

	/**
	 * @param IUserSession $session
	 * @return string
	 */
	protected function getCurrentUser(IUserSession $session) {
		$user = $session->getUser();
		if ($user instanceof IUser) {
			$user = $user->getUID();
		}

		return (string) $user;
	}
}
