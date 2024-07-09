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
use OCP\Notification\InvalidValueException;
use OCP\RichObjectStrings\IValidator;
use OCP\RichObjectStrings\InvalidObjectExeption;

class APIController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		protected ITimeFactory $timeFactory,
		protected IUserManager $userManager,
		protected IManager $notificationManager,
		protected IValidator $richValidator,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Generate a notification for a user
	 *
	 * @param string $userId ID of the user
	 * @param string $shortMessage Subject of the notification
	 * @param string $longMessage Message of the notification
	 * @param string $richSubject Subject of the notification with placeholders
	 * @param string $richSubjectParameters Rich objects to fill the subject placeholders, {@see \OCP\RichObjectStrings\Definitions}
	 * @param string $richMessage Message of the notification with placeholders
	 * @param string $richMessageParameters Rich objects to fill the message placeholders, {@see \OCP\RichObjectStrings\Definitions}
	 * @return DataResponse<Http::STATUS_OK, array<empty>, array{}>|DataResponse<Http::STATUS_BAD_REQUEST|Http::STATUS_NOT_FOUND|Http::STATUS_INTERNAL_SERVER_ERROR, null, array{}>
	 *
	 * 200: Notification generated successfully
	 * 400: Generating notification is not possible
	 * 404: User not found
	 */
	#[OpenAPI(scope: OpenAPI::SCOPE_ADMINISTRATION)]
	public function generateNotification(
		string $userId,
		string $shortMessage = '',
		string $longMessage = '',
		string $richSubject = '',
		array $richSubjectParameters = [],
		string $richMessage = '',
		array $richMessageParameters = [],
	): DataResponse {
		$user = $this->userManager->get($userId);

		if (!$user instanceof IUser) {
			return new DataResponse(null, Http::STATUS_NOT_FOUND);
		}

		if (($shortMessage === '' && $richSubject === '') || strlen($shortMessage) > 255) {
			return new DataResponse(null, Http::STATUS_BAD_REQUEST);
		}

		if ($longMessage !== '' && strlen($longMessage) > 4000) {
			return new DataResponse(null, Http::STATUS_BAD_REQUEST);
		}

		$notification = $this->notificationManager->createNotification();
		$datetime = $this->timeFactory->getDateTime();

		try {
			if ($richSubject !== '') {
				$this->richValidator->validate($richSubject, $richSubjectParameters);
			}
			if ($richMessage !== '') {
				$this->richValidator->validate($richMessage, $richMessageParameters);
			}
			$notification->setApp('admin_notifications')
				->setUser($user->getUID())
				->setDateTime($datetime)
				->setObject('admin_notifications', dechex($datetime->getTimestamp()))
				->setSubject(
					'ocs',
					[
						'parsed' => $shortMessage,
						'rich' => $richSubject,
						'parameters' => $richSubjectParameters,
					]
				);

			if ($longMessage !== '' || $richMessage !== '') {
				$notification->setMessage(
					'ocs',
					[
						'parsed' => $longMessage,
						'rich' => $richMessage,
						'parameters' => $richMessageParameters,
					]
				);
			}

			$this->notificationManager->notify($notification);
		} catch (InvalidObjectExeption $e) {
			return new DataResponse('Invalid rich object: '.$e->getMessage(), Http::STATUS_BAD_REQUEST);
		} catch (InvalidValueException $e) {
			return new DataResponse($e->getMessage(), Http::STATUS_BAD_REQUEST);
		} catch (\InvalidArgumentException) {
			return new DataResponse(null, Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new DataResponse();
	}
}
