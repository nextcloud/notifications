<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023, Joas Schilling <coding@schilljs.com>
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
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
use OCA\Notifications\ResponseDefinitions;
use OCA\Notifications\Service\ClientService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\UserStatus\IManager as IUserStatusManager;
use OCP\UserStatus\IUserStatus;

/**
 * @psalm-import-type NotificationsNotification from ResponseDefinitions
 * @psalm-import-type NotificationsNotificationAction from ResponseDefinitions
 */
class EndpointController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		protected Handler $handler,
		protected IManager $manager,
		protected IFactory $l10nFactory,
		protected IUserSession $session,
		protected ITimeFactory $timeFactory,
		protected IUserStatusManager $userStatusManager,
		protected ClientService $clientService,
		protected Push $push,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Get all notifications
	 *
	 * @param string $apiVersion Version of the API to use
	 * @return DataResponse<Http::STATUS_OK, NotificationsNotification[], array{'X-Nextcloud-User-Status': string}>|DataResponse<Http::STATUS_NO_CONTENT, null, array{X-Nextcloud-User-Status: string}>
	 *
	 * 200: Notifications returned
	 * 204: No app uses notifications
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

		$user = $this->session->getUser();
		$filter = $this->manager->createNotification();
		$filter->setUser($this->getCurrentUser());
		$language = $this->l10nFactory->getUserLanguage($user);
		$notifications = $this->handler->get($filter);

		$shouldFlush = $this->manager->defer();

		$hasActiveTalkDesktop = false;
		if ($user instanceof IUser) {
			$hasActiveTalkDesktop = $this->clientService->hasTalkDesktop(
				$user->getUID(),
				$this->timeFactory->getTime() - ClientService::DESKTOP_CLIENT_TIMEOUT
			);
		}

		$data = [];
		$notificationIds = [];
		foreach ($notifications as $notificationId => $notification) {
			/** @var INotification $notification */
			try {
				$notification = $this->manager->prepare($notification, $language);
			} catch (\InvalidArgumentException) {
				// The app was disabled, skip the notification
				continue;
			}

			$notificationIds[] = $notificationId;
			$data[] = $this->notificationToArray($notificationId, $notification, $apiVersion, $hasActiveTalkDesktop);
		}

		if ($shouldFlush) {
			$this->manager->flush();
		}

		$eTag = $this->generateETag($notificationIds);
		$response = new DataResponse($data, Http::STATUS_OK, $headers);
		if ($apiVersion !== 'v1') {
			$response->setETag($eTag);
		}
		return $response;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Get a notification
	 *
	 * @param string $apiVersion Version of the API to use
	 * @param int $id ID of the notification
	 * @return DataResponse<Http::STATUS_OK, NotificationsNotification, array{}>|DataResponse<Http::STATUS_NOT_FOUND, null, array{}>
	 *
	 * 200: Notification returned
	 * 404: Notification not found
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

		$user = $this->session->getUser();
		$language = $this->l10nFactory->getUserLanguage($user);

		try {
			$notification = $this->manager->prepare($notification, $language);
		} catch (\InvalidArgumentException $e) {
			// The app was disabled
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		$hasActiveTalkDesktop = false;
		if ($user instanceof IUser) {
			$hasActiveTalkDesktop = $this->clientService->hasTalkDesktop(
				$user->getUID(),
				$this->timeFactory->getTime() - ClientService::DESKTOP_CLIENT_TIMEOUT
			);
		}

		return new DataResponse($this->notificationToArray($id, $notification, $apiVersion, $hasActiveTalkDesktop));
	}

	/**
	 * @NoAdminRequired
	 *
	 * Check if notification IDs exist
	 *
	 * @param string $apiVersion Version of the API to use
	 * @param int[] $ids IDs of the notifications to check
	 * @return DataResponse<Http::STATUS_OK|Http::STATUS_BAD_REQUEST, int[], array{}>
	 *
	 * 200: Existing notification IDs returned
	 * 400: Too many notification IDs requested
	 */
	public function confirmIdsForUser(string $apiVersion, array $ids): DataResponse {
		if (!$this->manager->hasNotifiers()) {
			return new DataResponse([], Http::STATUS_OK);
		}

		if (empty($ids)) {
			return new DataResponse([], Http::STATUS_OK);
		}

		if (count($ids) > 200) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		$ids = array_unique(array_filter(array_map(
			static fn ($id) => is_numeric($id) ? (int) $id : 0,
			$ids
		)));

		$existingIds = $this->handler->confirmIdsForUser($this->getCurrentUser(), $ids);
		return new DataResponse($existingIds, Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 *
	 * Delete a notification
	 *
	 * @param int $id ID of the notification
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array{}>|DataResponse<Http::STATUS_FORBIDDEN|Http::STATUS_NOT_FOUND, null, array{}>
	 *
	 * 200: Notification deleted successfully
	 * 403: Deleting notification for impersonated user is not allowed
	 * 404: Notification not found
	 */
	public function deleteNotification(int $id): DataResponse {
		if ($id === 0) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		if ($this->session->getImpersonatingUserID() !== null) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		try {
			$notification = $this->handler->getById($id, $this->getCurrentUser());
			$deleted = $this->handler->deleteById($id, $this->getCurrentUser(), $notification);

			if ($deleted) {
				$this->push->pushDeleteToDevice($this->getCurrentUser(), [$id], $notification->getApp());
			}
		} catch (NotificationNotFoundException $e) {
		}

		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 *
	 * Delete all notifications
	 *
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array{}>|DataResponse<Http::STATUS_FORBIDDEN, null, array{}>
	 *
	 * 200: All notifications deleted successfully
	 * 403: Deleting notification for impersonated user is not allowed
	 */
	public function deleteAllNotifications(): DataResponse {
		if ($this->session->getImpersonatingUserID() !== null) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		$shouldFlush = $this->manager->defer();

		$deletedSomething = $this->handler->deleteByUser($this->getCurrentUser());
		if ($deletedSomething) {
			$this->push->pushDeleteToDevice($this->getCurrentUser(), null);
		}

		if ($shouldFlush) {
			$this->manager->flush();
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
	 * @param bool $hasActiveTalkDesktop
	 * @return NotificationsNotification
	 */
	protected function notificationToArray(int $notificationId, INotification $notification, string $apiVersion, bool $hasActiveTalkDesktop = false): array {
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
			if ($this->request->isUserAgent([IRequest::USER_AGENT_TALK_DESKTOP])) {
				$shouldNotify = $notification->getApp() === 'spreed';
			} else {
				$shouldNotify = !$hasActiveTalkDesktop || $notification->getApp() !== 'spreed';
			}

			$data = array_merge($data, [
				'subjectRich' => $notification->getRichSubject(),
				'subjectRichParameters' => $notification->getRichSubjectParameters(),
				'messageRich' => $notification->getRichMessage(),
				'messageRichParameters' => $notification->getRichMessageParameters(),
				'icon' => $notification->getIcon(),
				'shouldNotify' => $shouldNotify,
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
	 * @return NotificationsNotificationAction
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
