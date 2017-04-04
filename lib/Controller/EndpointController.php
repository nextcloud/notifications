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

namespace OCA\Notifications\Controller;

use OCA\Notifications\Handler;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;

class EndpointController extends OCSController {
	/** @var Handler */
	private $handler;

	/** @var IManager */
	private $manager;

	/** @var IUserSession */
	private $session;

	/** @var IConfig */
	private $config;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param Handler $handler
	 * @param IManager $manager
	 * @param IConfig $config
	 * @param IUserSession $session
	 */
	public function __construct($appName, IRequest $request, Handler $handler, IManager $manager, IConfig $config, IUserSession $session) {
		parent::__construct($appName, $request);

		$this->handler = $handler;
		$this->manager = $manager;
		$this->config = $config;
		$this->session = $session;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $apiVersion
	 * @return DataResponse
	 */
	public function listNotifications($apiVersion) {
		// When there are no apps registered that use the notifications
		// We stop polling for them.
		if (!$this->manager->hasNotifiers()) {
			return new DataResponse(null, Http::STATUS_NO_CONTENT);
		}

		$filter = $this->manager->createNotification();
		$filter->setUser($this->getCurrentUser());
		$language = $this->config->getUserValue($this->getCurrentUser(), 'core', 'lang', null);

		$notifications = $this->handler->get($filter);

		$data = [];
		$notificationIds = [];
		foreach ($notifications as $notificationId => $notification) {
			/** @var INotification $notification */
			try {
				$notification = $this->manager->prepare($notification, $language);
			} catch (\InvalidArgumentException $e) {
				// The app was disabled, skip the notification
				continue;
			}

			$notificationIds[] = $notificationId;
			$data[] = $this->notificationToArray($notificationId, $notification, $apiVersion);
		}

		$etag = $this->generateEtag($notificationIds);
		if ($apiVersion !== 'v1' && $this->request->getHeader('If-None-Match') === $etag) {
			return new DataResponse([], Http::STATUS_NOT_MODIFIED);
		}

		return new DataResponse($data, Http::STATUS_OK, ['ETag' => $etag]);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $apiVersion
	 * @param int $id
	 * @return DataResponse
	 */
	public function getNotification($apiVersion, $id) {
		if (!$this->manager->hasNotifiers()) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		if (!is_int($id) || $id === 0) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		$notification = $this->handler->getById($id, $this->getCurrentUser());

		if (!($notification instanceof INotification)) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		$language = $this->config->getUserValue($this->getCurrentUser(), 'core', 'lang', null);

		try {
			$notification = $this->manager->prepare($notification, $language);
		} catch (\InvalidArgumentException $e) {
			// The app was disabled
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		return new DataResponse($this->notificationToArray($id, $notification, $apiVersion));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return DataResponse
	 */
	public function deleteNotification($id = 0) {
		if (!is_int($id) || $id === 0) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}
		$id = (int) $id;

		$this->handler->deleteById($id, $this->getCurrentUser());
		return new DataResponse();
	}

	/**
	 * Get an Etag for the notification ids
	 *
	 * @param array $notifications
	 * @return string
	 */
	protected function generateEtag(array $notifications) {
		return md5(json_encode($notifications));
	}

	/**
	 * @param int $notificationId
	 * @param INotification $notification
	 * @param string $apiVersion
	 * @return array
	 */
	protected function notificationToArray($notificationId, INotification $notification, $apiVersion) {
		$data = [
			'notification_id' => $notificationId,
			'app' => $notification->getApp(),
			'user' => $notification->getUser(),
			'datetime' => $notification->getDateTime()->format('c'),
			'object_type' => $notification->getObjectType(),
			'object_id' => $notification->getObjectId(),
			'subject' => $notification->getParsedSubject(),
			'message' => $notification->getParsedMessage(),
			'link' => $notification->getLink(),
		];

		if ($apiVersion !== 'v1') {
			$data = array_merge($data, [
				'subjectRich' => $notification->getRichSubject(),
				'subjectRichParameters' => $notification->getRichSubjectParameters(),
				'messageRich' => $notification->getRichMessage(),
				'messageRichParameters' => $notification->getRichMessageParameters(),
				'icon' => $notification->getIcon(),
			]);
		}

		$data['actions'] = [];
		foreach ($notification->getParsedActions() as $action) {
			$data['actions'][] = $this->actionToArray($action);
		}

		return $data;
	}

	/**
	 * @param IAction $action
	 * @return array
	 */
	protected function actionToArray(IAction $action) {
		return [
			'label' => $action->getParsedLabel(),
			'link' => $action->getLink(),
			'type' => $action->getRequestType(),
			'primary' => $action->isPrimary(),
		];
	}

	/**
	 * @return string
	 */
	protected function getCurrentUser() {
		$user = $this->session->getUser();
		if ($user instanceof IUser) {
			$user = $user->getUID();
		}

		return (string) $user;
	}
}
