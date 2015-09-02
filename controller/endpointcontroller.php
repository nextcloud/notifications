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
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
<<<<<<< HEAD
use OCP\AppFramework\Http\Response;
=======
>>>>>>> 29db962... First attempt at frontend
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
		$filter = $this->manager->createNotification();
		$filter->setUser($this->user);
		$language = $this->config->getUserValue($this->user, 'core', 'lang', null);

		$notifications = $this->handler->get($filter);

		$data = [];
<<<<<<< HEAD
		foreach ($notifications as $notificationId => $notification) {
			$notification = $this->manager->prepare($notification, $language);
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
=======
		foreach ($notifications as $notification) {
			$this->manager->prepare($notification, $language);
			$data[] = $this->notificationToArray($notification);
		}

		return new JSONResponse([[
		'notification_id' => 123,
		'app' => 'testing',
		'user' => 'user',
		'timestamp' => time(),
		'object_type' => 'type',
		'object_id' => 1337,
		'subject' => 'I\'m a short subject',
		'message' => 'Maybe we need longer descriptions later, no usecase in mind at the moment',
		'link' => '/get/request/when/subject/or/message/is/clicked',
		'icon' => 'testing-app-icon',
		'actions' => [
			[
				'label' => 'Action',
				'icon' => 'testing-app-icon-action',
				'link' => '/post/request/sending',
				'type' => 'POST',
			],
			[
				'label' => 'No Action',
				'icon' => 'testing-app-icon-no',
				'link' => '/delete/request/sending',
				'type' => 'DELETE',
			],
		],
	]]);
	}

	/**
	 * @param INotification $notification
	 * @return array
	 */
	protected function notificationToArray(INotification $notification) {
		$data = [
>>>>>>> 29db962... First attempt at frontend
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
<<<<<<< HEAD
			'link' => $action->getLink(),
			'type' => $action->getRequestType(),
			'icon' => $action->getIcon(),
=======
			'icon' => $action->getIcon(),
			'link' => $action->getLink(),
			'type' => $action->getRequestType(),
>>>>>>> 29db962... First attempt at frontend
		];
	}
}
