<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;

class APIController extends OCSController {
	/** @var ITimeFactory */
	protected $timeFactory;

	/** @var IUserManager */
	protected $userManager;

	/** @var IManager */
	protected $notificationManager;

	public function __construct(
		string $appName,
		IRequest $request,
		ITimeFactory $timeFactory,
		IUserManager $userManager,
		IManager $notificationManager,
	) {
		parent::__construct($appName, $request);

		$this->timeFactory = $timeFactory;
		$this->userManager = $userManager;
		$this->notificationManager = $notificationManager;
	}

	/**
	 * Generate a notification for a user
	 *
	 * @param string $userId ID of the user
	 * @param string $shortMessage Subject of the notification
	 * @param string $longMessage Message of the notification
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array{}>|DataResponse<Http::STATUS_BAD_REQUEST|Http::STATUS_NOT_FOUND|Http::STATUS_INTERNAL_SERVER_ERROR, null, array{}>
	 *
	 * 200: Notification generated successfully
	 * 400: Generating notification is not possible
	 * 404: User not found
	 */
	#[OpenAPI(scope: OpenAPI::SCOPE_ADMINISTRATION)]
	public function generateNotification(string $userId, string $shortMessage, string $longMessage = ''): DataResponse {
		$user = $this->userManager->get($userId);

		if (!$user instanceof IUser) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		if ($shortMessage === '' || strlen($shortMessage) > 255) {
			return new DataResponse(null, Http::STATUS_BAD_REQUEST);
		}

		if ($longMessage !== '' && strlen($longMessage) > 4000) {
			return new DataResponse(null, Http::STATUS_BAD_REQUEST);
		}

		$notification = $this->notificationManager->createNotification();
		$datetime = $this->timeFactory->getDateTime();

		try {
			$notification->setApp('admin_notifications')
				->setUser($user->getUID())
				->setDateTime($datetime)
				->setObject('admin_notifications', dechex($datetime->getTimestamp()))
				->setSubject('ocs', [$shortMessage]);

			if ($longMessage !== '') {
				$notification->setMessage('ocs', [$longMessage]);
			}

			$this->notificationManager->notify($notification);
		} catch (\InvalidArgumentException) {
			return new DataResponse(null, Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new DataResponse();
	}
}
