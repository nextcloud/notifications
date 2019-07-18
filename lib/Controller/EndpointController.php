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

use OCA\Notifications\Exceptions\NotificationNotFoundException;
use OCA\Notifications\Handler;
use OCA\Notifications\Push;
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
	/** @var IConfig */
	private $config;
	/** @var IUserSession */
	private $session;
	/** @var Push */
	private $push;


	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param Handler $handler
	 * @param IManager $manager
	 * @param IConfig $config
	 * @param IUserSession $session
	 * @param Push $push
	 */
	public function __construct(string $appName,
								IRequest $request,
								Handler $handler,
								IManager $manager,
								IConfig $config,
								IUserSession $session,
								Push $push) {
		parent::__construct($appName, $request);

		$this->handler = $handler;
		$this->manager = $manager;
		$this->config = $config;
		$this->session = $session;
		$this->push = $push;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $apiVersion
	 * @return DataResponse
	 */
	public function listNotifications(string $apiVersion): DataResponse {
		// When there are no apps registered that use the notifications
		// We stop polling for them.
		if (!$this->manager->hasNotifiers()) {
			return new DataResponse(null, Http::STATUS_NO_CONTENT);
		}

		$filter = $this->manager->createNotification();
		$filter->setUser($this->getCurrentUser());
		$language = $this->config->getUserValue($this->getCurrentUser(), 'core', 'lang', null);
		$language = $language ?? $this->config->getSystemValue('default_language', 'en');

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

		$eTag = $this->generateETag($notificationIds);
		if ($apiVersion !== 'v1' && $this->request->getHeader('If-None-Match') === $eTag) {
			return new DataResponse([], Http::STATUS_NOT_MODIFIED);
		}

		return new DataResponse($data, Http::STATUS_OK, ['ETag' => $eTag]);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $apiVersion
	 * @param int $id
	 * @return DataResponse
	 */
	public function getNotification(string $apiVersion, int $id): DataResponse {
		if (!$this->manager->hasNotifiers()) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		if ($id === 0) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		try {
			$notification = $this->handler->getById($id, $this->getCurrentUser());
		} catch (NotificationNotFoundException $e) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		$language = $this->config->getUserValue($this->getCurrentUser(), 'core', 'lang', null);
		$language = $language ?? $this->config->getSystemValue('default_language', 'en');

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
	public function deleteNotification(int $id): DataResponse {
		if ($id === 0) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		$this->handler->deleteById($id, $this->getCurrentUser());
		$this->push->pushDeleteToDevice($this->getCurrentUser(), $id);
		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function deleteAllNotifications(): DataResponse {
		$this->handler->deleteByUser($this->getCurrentUser());
		$this->push->pushDeleteToDevice($this->getCurrentUser(), 0);
		return new DataResponse();
	}

	/**
	 * Get an ETag for the notification ids
	 *
	 * @param array $notifications
	 * @return string
	 */
	protected function generateETag(array $notifications): string {
		return md5(json_encode($notifications));
	}

	/**
	 * @param int $notificationId
	 * @param INotification $notification
	 * @param string $apiVersion
	 * @return array
	 */
	protected function notificationToArray(int $notificationId, INotification $notification, string $apiVersion): array {
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
	protected function actionToArray(IAction $action): array {
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
	protected function getCurrentUser(): string {
		$user = $this->session->getUser();
		if ($user instanceof IUser) {
			$user = $user->getUID();
		}

		return (string) $user;
	}
}
