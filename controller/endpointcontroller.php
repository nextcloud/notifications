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

namespace OCA\Notifications\Controller;

use OC\Notification\IAction;
use OC\Notification\IManager;
use OC\Notification\INotification;
use OCA\Notifications\Handler;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Response;
use OCP\IConfig;
use OCP\IRequest;

class EndpointController extends Controller {
	/** @var Handler */
	private $handler;

	/** @var IManager */
	private $manager;

	/** @var string */
	private $user;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param Handler $handler
	 * @param IManager $manager
	 * @param IConfig $config
	 * @param string $userId
	 */
	public function __construct($appName, IRequest $request, Handler $handler, IManager $manager, IConfig $config, $userId) {
		parent::__construct($appName, $request);

		$this->handler = $handler;
		$this->manager = $manager;
		$this->config = $config;
		$this->user = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function get() {
		// When there are no apps registered that use the notifications
		// We stop polling for them.
		if (!$this->manager->hasNotifiers()) {
			$response = new Response();
			$response->setStatus(Http::STATUS_NO_CONTENT);
			return $response;
		}

		$filter = $this->manager->createNotification();
		$filter->setUser($this->user);
		$language = $this->config->getUserValue($this->user, 'core', 'lang', null);

		$notifications = $this->handler->get($filter);

		$data = [];
		$notificationIds = [];
		foreach ($notifications as $notificationId => $notification) {
			try {
				$notification = $this->manager->prepare($notification, $language);
			} catch (\InvalidArgumentException $e) {
				// The app was disabled, skip the notification
				continue;
			}

			$notificationIds[] = $notificationId;
			$data[] = $this->notificationToArray($notificationId, $notification);
		}

		$response = new JSONResponse($data);
		$response->setETag($this->generateEtag($notificationIds));
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return Response
	 */
	public function delete($id) {
		$this->handler->deleteById($id, $this->user);
		return new Response();
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
			'timestamp' => $notification->getTimestamp(),
			'object_type' => $notification->getObjectType(),
			'object_id' => $notification->getObjectId(),
			'subject' => $notification->getParsedSubject(),
			'message' => $notification->getParsedMessage(),
			'link' => $notification->getLink(),
			'icon' => $notification->getIcon(),
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
			'icon' => $action->getIcon(),
		];
	}
}
