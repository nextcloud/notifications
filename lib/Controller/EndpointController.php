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

namespace OCA\Notifications\Controller;

use OCA\Notifications\Handler;
use OCP\AppFramework\Http;
use OCP\AppFramework\Controller;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;

class EndpointController extends Controller {
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
	 * @return \OC_OCS_Result
	 */
	public function listNotifications() {
		// When there are no apps registered that use the notifications
		// We stop polling for them.
		if (!$this->manager->hasNotifiers()) {
			return new \OC_OCS_Result(null, Http::STATUS_NO_CONTENT);
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
			$data[] = $this->notificationToArray($notificationId, $notification);
		}

		return new \OC_OCS_Result(
			$data,
			100, // HTTP::STATUS_OK, TODO: <status>failure</status><statuscode>200</statuscode>
			null,
			['ETag' => $this->generateEtag($notificationIds)]
		);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param array $parameters
	 * @return \OC_OCS_Result
	 */
	public function getNotification(array $parameters) {
		if (!$this->manager->hasNotifiers()) {
			return new \OC_OCS_Result(null, Http::STATUS_NOT_FOUND);
		}

		if (!isset($parameters['id'])) {
			return new \OC_OCS_Result(null, HTTP::STATUS_NOT_FOUND);
		}
		$id = (int) $parameters['id'];

		$notification = $this->handler->getById($id, $this->getCurrentUser());

		if (!($notification instanceof INotification)) {
			return new \OC_OCS_Result(null, HTTP::STATUS_NOT_FOUND);
		}

		$language = $this->config->getUserValue($this->getCurrentUser(), 'core', 'lang', null);

		try {
			$notification = $this->manager->prepare($notification, $language);
		} catch (\InvalidArgumentException $e) {
			// The app was disabled
			return new \OC_OCS_Result(null, HTTP::STATUS_NOT_FOUND);
		}

		return new \OC_OCS_Result(
			$this->notificationToArray($id, $notification),
			100 // HTTP::STATUS_OK TODO: <status>failure</status><statuscode>200</statuscode>
		);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param array $parameters
	 * @return \OC_OCS_Result
	 */
	public function deleteNotification(array $parameters) {
		if (!isset($parameters['id'])) {
			return new \OC_OCS_Result(null, HTTP::STATUS_NOT_FOUND);
		}
		$id = (int) $parameters['id'];

		$this->handler->deleteById($id, $this->getCurrentUser());
		return new \OC_OCS_Result();
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
	 * @return array
	 */
	protected function notificationToArray($notificationId, INotification $notification) {
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
			'actions' => [],
		];

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
