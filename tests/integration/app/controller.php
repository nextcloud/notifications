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

use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\Notification\IManager;

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
		$notification->setApp($this->request->getParam('app', 'notificationsintegrationtesting'))
			->setDateTime(\DateTime::createFromFormat('U', $this->request->getParam('timestamp', 1449585176))) // 2015-12-08T14:32:56+00:00
			->setUser($this->request->getParam('user', 'test1'))
			->setSubject($this->request->getParam('subject', 'testing'))
			->setLink($this->request->getParam('link', 'https://www.owncloud.org/'))
			->setMessage($this->request->getParam('message', 'message'))
			->setObject($this->request->getParam('object_type', 'object'), $this->request->getParam('object_id', 23));

		$this->manager->notify($notification);

		return new \OC_OCS_Result();
	}

	/**
	 * @NoCSRFRequired
	 *
	 * @return \OC_OCS_Result
	 */
	public function deleteNotifications() {
		$notification = $this->manager->createNotification();
		$this->manager->markProcessed($notification);

		return new \OC_OCS_Result();
	}
}
