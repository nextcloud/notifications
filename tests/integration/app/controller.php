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

namespace OCA\NotificationsIntegrationTesting;

use OC\Notification\IManager;
use OCP\AppFramework\Http;
use OCP\IRequest;

class Controller extends \OCP\AppFramework\Controller {

	/** @var IManager */
	private $manager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IManager $manager
	 */
	public function __construct($appName, IRequest $request, IManager $manager) {
		parent::__construct($appName, $request);

		$this->manager = $manager;
	}

	/**
	 * @NoCSRFRequired
	 *
	 * @return \OC_OCS_Result
	 */
	public function addNotification() {
		$notification = $this->manager->createNotification();
		$notification->setApp('notificationsintegrationtesting')
			->setDateTime(\DateTime::createFromFormat('U', 1449585176)) // 2015-12-08T14:32:56+00:00
			->setUser('test1')
			->setSubject('testing')
			->setLink('https://www.owncloud.org/')
			->setMessage('message')
			->setObject('object', 23);

		$this->manager->notify($notification);

		return new \OC_OCS_Result();
	}

	/**
	 * @NoCSRFRequired
	 *
	 * @return \OC_OCS_Result
	 */
	public function reset() {
		$notification = $this->manager->createNotification();
		$notification->setApp('notificationsintegrationtesting');
		$this->manager->markProcessed($notification);

		return new \OC_OCS_Result();
	}
}
