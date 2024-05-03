<?php
/**
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\NotificationsIntegrationTesting\Controller;

use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use OCP\Notification\IManager;

class EndpointController extends OCSController {
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
	 * @param string $userId
	 * @return DataResponse
	 */
	public function addNotification(string $userId = 'test1') {
		$notification = $this->manager->createNotification();
		$notification->setApp($this->request->getParam('app', 'notificationsintegrationtesting'))
			->setDateTime(\DateTime::createFromFormat('U', $this->request->getParam('timestamp', 1449585176))) // 2015-12-08T14:32:56+00:00
			->setUser($this->request->getParam('user', $userId))
			->setSubject($this->request->getParam('subject', 'testing'))
			->setLink($this->request->getParam('link', 'https://example.tld/'))
			->setMessage($this->request->getParam('message', 'message'))
			->setObject($this->request->getParam('object_type', 'object'), $this->request->getParam('object_id', 23));

		$this->manager->notify($notification);

		return new DataResponse();
	}

	/**
	 * @NoCSRFRequired
	 *
	 * @return DataResponse
	 */
	public function deleteNotifications() {
		$notification = $this->manager->createNotification();
		$this->manager->markProcessed($notification);

		return new DataResponse();
	}
}
