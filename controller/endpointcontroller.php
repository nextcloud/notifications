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

use OCA\Notifications\Handler;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Response;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;

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
		$filter = $this->manager->createNotification();
		$filter->setUser($this->user);
		$language = $this->config->getUserValue($this->user, 'core', 'lang', null);

		$notifications = $this->handler->get($filter);

		$data = [];
		foreach ($notifications as $notificationId => $notification) {
			$this->manager->prepare($notification, $language);
			$data[] = $this->notificationToArray($notificationId, $notification);
		}

		return new JSONResponse($data);
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
			'icon' => $action->getIcon(),
			'link' => $action->getLink(),
			'type' => $action->getRequestType(),
		];
	}
}
