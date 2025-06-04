<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\NotificationsIntegrationTesting\Controller;

use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use OCP\Notification\IManager;

class EndpointController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		protected IManager $manager,
	) {
		parent::__construct($appName, $request);
	}

	#[NoCSRFRequired]
	#[ApiRoute(verb: 'POST', url: '/notifications')]
	public function addNotification(string $userId = 'test1'): DataResponse {
		$notification = $this->manager->createNotification();
		$notification->setApp($this->request->getParam('app', 'notificationsintegrationtesting'))
			->setDateTime(\DateTime::createFromFormat('U', (string)$this->request->getParam('timestamp', '1449585176'))) // 2015-12-08T14:32:56+00:00
			->setUser($this->request->getParam('user', $userId))
			->setSubject($this->request->getParam('subject', 'testing'))
			->setLink($this->request->getParam('link', 'https://example.tld/'))
			->setMessage($this->request->getParam('message', 'message'))
			->setObject($this->request->getParam('object_type', 'object'), (string)$this->request->getParam('object_id', '23'));

		$this->manager->notify($notification);

		return new DataResponse();
	}

	#[NoCSRFRequired]
	#[ApiRoute(verb: 'DELETE', url: '')]
	public function deleteNotifications(): DataResponse {
		$notification = $this->manager->createNotification();
		$this->manager->markProcessed($notification);

		return new DataResponse();
	}
}
