<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Controller;

use OCA\Notifications\App;
use OCA\Notifications\ResponseDefinitions;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Notification\IManager;
use OCP\Notification\IncompleteNotificationException;
use OCP\Notification\InvalidValueException;
use OCP\RichObjectStrings\InvalidObjectExeption;
use OCP\RichObjectStrings\IValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @psalm-import-type NotificationsRichObjectParameter from ResponseDefinitions
 * @psalm-import-type NotificationsRichObjectParameters from ResponseDefinitions
 */
class APIController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		protected ITimeFactory $timeFactory,
		protected IUserManager $userManager,
		protected IUserSession $userSession,
		protected IManager $notificationManager,
		protected App $notificationApp,
		protected IValidator $richValidator,
		protected IL10N $l,
		protected LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Generate a notification for a user (deprecated, use v3 instead)
	 *
	 * @param string $userId ID of the user
	 * @param string $shortMessage Subject of the notification
	 * @param string $longMessage Message of the notification
	 * @return DataResponse<Http::STATUS_OK, list<empty>, array{}>|DataResponse<Http::STATUS_BAD_REQUEST|Http::STATUS_NOT_FOUND|Http::STATUS_INTERNAL_SERVER_ERROR, null, array{}>
	 * @deprecated 30.0.0
	 *
	 * 200: Notification generated successfully
	 * 400: Generating notification is not possible
	 * 404: User not found
	 */
	#[OpenAPI(scope: OpenAPI::SCOPE_ADMINISTRATION)]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion}/admin_notifications/{userId}', requirements: ['apiVersion' => '(v1|v2)'])]
	public function generateNotification(string $userId, string $shortMessage, string $longMessage = ''): DataResponse {
		$response = $this->generateNotificationV3($userId, $shortMessage, $longMessage);
		if ($response->getStatus() === Http::STATUS_OK) {
			return new DataResponse();
		}

		// Translate to old status code
		$error = $response->getData()['error'] ?? null;
		$code = match($error) {
			'user' => Http::STATUS_NOT_FOUND,
			'subject',
			'message' => Http::STATUS_BAD_REQUEST,
			default => Http::STATUS_INTERNAL_SERVER_ERROR,
		};
		return new DataResponse(null, $code);
	}

	/**
	 * Generate a notification with rich object parameters for a user
	 *
	 * @param string $userId ID of the user
	 * @param string $subject Subject of the notification
	 * @param string $message Message of the notification
	 * @param NotificationsRichObjectParameters $subjectParameters Rich objects to fill the subject placeholders, {@see \OCP\RichObjectStrings\Definitions}
	 * @param NotificationsRichObjectParameters $messageParameters Rich objects to fill the message placeholders, {@see \OCP\RichObjectStrings\Definitions}
	 * @return DataResponse<Http::STATUS_OK, array{id: int}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Notification generated successfully, returned id is the notification ID for future delete requests
	 * 400: Provided data was invalid, check error field of the response of log file for details
	 */
	#[OpenAPI(scope: OpenAPI::SCOPE_ADMINISTRATION)]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion3}/admin_notifications/{userId}', requirements: ['apiVersion3' => '(v3)'])]
	public function generateNotificationV3(
		string $userId,
		string $subject = '',
		string $message = '',
		array $subjectParameters = [],
		array $messageParameters = [],
	): DataResponse {
		$user = $this->userManager->get($userId);

		if (!$user instanceof IUser) {
			return new DataResponse(['error' => 'user'], Http::STATUS_BAD_REQUEST);
		}

		if ($subject === '' || strlen($subject) > 255) {
			return new DataResponse(['error' => 'subject'], Http::STATUS_BAD_REQUEST);
		}

		if ($message !== '' && strlen($message) > 4000) {
			return new DataResponse(['error' => 'message'], Http::STATUS_BAD_REQUEST);
		}

		$notification = $this->notificationManager->createNotification();
		$datetime = $this->timeFactory->getDateTime();

		try {
			if (!empty($subjectParameters)) {
				$this->richValidator->validate($subject, $subjectParameters);
			}
			if ($message !== '' && !empty($messageParameters)) {
				$this->richValidator->validate($message, $messageParameters);
			}
			$notification->setApp('admin_notifications')
				->setUser($user->getUID())
				->setDateTime($datetime)
				->setObject('admin_notifications', dechex($datetime->getTimestamp()))
				->setSubject(
					'ocs',
					[
						'subject' => $subject,
						'parameters' => $subjectParameters,
					]
				);

			if ($message !== '') {
				$notification->setMessage(
					'ocs',
					[
						'message' => $message,
						'parameters' => $messageParameters,
					]
				);
			}

			$this->notificationManager->notify($notification);
		} catch (InvalidObjectExeption $e) {
			$this->logger->error('Invalid rich object parameter provided: ' . $e->getMessage(), ['exception' => $e]);
			return new DataResponse(['error' => 'parameters'], Http::STATUS_BAD_REQUEST);
		} catch (InvalidValueException|IncompleteNotificationException $e) {
			$this->logger->error('Invalid value for notification provided: ' . $e->getMessage(), ['exception' => $e]);
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}

		return new DataResponse(['id' => (int)$this->notificationApp->getLastInsertedId()]);
	}

	/**
	 * Send a test notification to push registered mobile apps
	 *
	 * Required capability: `ocs-endpoints > test-push`
	 *
	 * @return DataResponse<Http::STATUS_OK, array{message: string, nid: int}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array{}>
	 *
	 * 200: Test notification generated successfully, but the device should still show the message to the user
	 * 400: Test notification could not be generated, show the message to the user
	 */
	#[NoAdminRequired]
	#[OpenAPI(scope: 'push')]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion3}/test/self', requirements: ['apiVersion3' => '(v3)'])]
	public function selfTestPush(): DataResponse {
		if (!$this->notificationManager->isFairUseOfFreePushService()) {
			$message = $this->l->t('We want to keep offering our push notification service for free, but large number of users overload our infrastructure. For this reason we have to rate-limit the use of push notifications. If you need this feature, consider using Nextcloud Enterprise.');
			return new DataResponse(
				['message' => $message],
				Http::STATUS_BAD_REQUEST,
			);
		}

		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse(
				['message' => $this->l->t('User not found')],
				Http::STATUS_BAD_REQUEST,
			);
		}

		$notification = $this->notificationManager->createNotification();
		$datetime = $this->timeFactory->getDateTime();
		$isTalkApp = $this->request->isUserAgent([
			IRequest::USER_AGENT_TALK_ANDROID,
			IRequest::USER_AGENT_TALK_DESKTOP,
			IRequest::USER_AGENT_TALK_IOS,
		]);
		$app = $isTalkApp ? 'admin_notification_talk' : 'admin_notifications';

		$output = new BufferedOutput();
		try {
			$notification->setApp($app)
				->setUser($user->getUID())
				->setDateTime($datetime)
				->setObject('admin_notifications', dechex($datetime->getTimestamp()))
				->setSubject('self', ['Testing push notifications']);

			$this->notificationApp->setOutput($output);
			$this->notificationManager->notify($notification);

			/** @var int $nid */
			$nid = $this->notificationApp->getLastInsertedId();
			return new DataResponse(['message' => $output->fetch(), 'nid' => $nid]);
		} catch (\InvalidArgumentException $e) {
			$this->logger->error('Self testing push notification failed: ' . $e->getMessage(), ['exception' => $e]);
			return new DataResponse(
				['message' => $this->l->t('An unexpected error occurred, ask your administration to check the logs.')],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}
}
