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
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\UserStatus\IManager as IUserStatusManager;
use OCP\UserStatus\IUserStatus;

class EndpointController extends OCSController {
	/** @var Handler */
	private $handler;
	/** @var IManager */
	private $manager;
	/** @var IFactory */
	private $l10nFactory;
	/** @var IUserSession */
	private $session;
	/** @var IUserStatusManager */
	private $userStatusManager;
	/** @var Push */
	private $push;

	public function __construct(string $appName,
								IRequest $request,
								Handler $handler,
								IManager $manager,
								IFactory $l10nFactory,
								IUserSession $session,
								IUserStatusManager $userStatusManager,
								Push $push) {
		parent::__construct($appName, $request);

		$this->handler = $handler;
		$this->manager = $manager;
		$this->l10nFactory = $l10nFactory;
		$this->session = $session;
		$this->userStatusManager = $userStatusManager;
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
		$userStatus = $this->userStatusManager->getUserStatuses([
			$this->getCurrentUser(),
		]);

		$headers = ['X-Nextcloud-User-Status' => IUserStatus::ONLINE];
		if (isset($userStatus[$this->getCurrentUser()])) {
			$userStatus = $userStatus[$this->getCurrentUser()];
			$headers['X-Nextcloud-User-Status'] = $userStatus->getStatus();
		}

		// When there are no apps registered that use the notifications
		// We stop polling for them.
		if (!$this->manager->hasNotifiers()) {
			return new DataResponse(null, Http::STATUS_NO_CONTENT, $headers);
		}

		$filter = $this->manager->createNotification();
		$filter->setUser($this->getCurrentUser());
		$language = $this->l10nFactory->getUserLanguage($this->session->getUser());
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

		$headers['ETag'] = $eTag;
		if ($apiVersion !== 'v1' && $this->request->getHeader('If-None-Match') === $eTag) {
			return new DataResponse([], Http::STATUS_NOT_MODIFIED, $headers);
		}

		return new DataResponse($data, Http::STATUS_OK, $headers);
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

		$language = $this->l10nFactory->getUserLanguage($this->session->getUser());

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

		if ($this->session->getImpersonatingUserID() !== null) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		try {
			$deleted = $this->handler->deleteById($id, $this->getCurrentUser());

			if ($deleted) {
				$this->push->pushDeleteToDevice($this->getCurrentUser(), $id);
			}
		} catch (NotificationNotFoundException $e) {
		}

		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function deleteAllNotifications(): DataResponse {
		if ($this->session->getImpersonatingUserID() !== null) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		$deletedSomething = $this->handler->deleteByUser($this->getCurrentUser());
		if ($deletedSomething) {
			$this->push->pushDeleteToDevice($this->getCurrentUser(), 0);
		}
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
