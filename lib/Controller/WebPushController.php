<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Controller;

use OCA\Notifications\WebPushClient;
use OC\Authentication\Token\IProvider;
use OC\Security\IdentityProof\Manager;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\Authentication\Exceptions\InvalidTokenException;
use OCP\Authentication\Token\IToken;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IAppConfig;
use OCP\IDBConnection;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserSession;
use Symfony\Component\Uid\Uuid;

enum NewSubStatus: int {
	case CREATED = 0;
	case UPDATED = 1;
	case ERROR = 2;
}

enum ActivationSubStatus: int {
	case CREATED = 0;
	case OK = 1;
	case NO_TOKEN = 2;
	case NO_SUB = 3;
}

#[OpenAPI(scope: 'push')]
class WebPushController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		protected IAppConfig $appConfig,
		protected IDBConnection $db,
		protected ISession $session,
		protected IUserSession $userSession,
		protected IProvider $tokenProvider,
		protected Manager $identityProof,
	) {
		parent::__construct($appName, $request);
	}


	/**
	 * Return the server VAPID public key
	 *
	 * @return DataResponse<Http::STATUS_OK, array{vapid: string}, array{}>
	 *
	 * 200: The VAPID key
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/{apiVersion}/webpush/vapid', requirements: ['apiVersion' => '(v2)'])]
	public function getVapid(string $apiVersion): DataResponse {
		return new DataResponse(['vapid' => $this->getWPClient()->getVapidPublicKey()], Http::STATUS_OK);
	}

	/**
	 * Register a subscription for push notifications
	 *
     * @param string $endpoint Push Server URL (RFC8030)
     * @param string $uaPublicKey Public key of the device, uncompress base64url encoded (RFC8291)
     * @param string $auth Authentication tag, base64url encoded (RFC8291)
	 * @param string $apptypes comma seperated list of types used to filter incoming notifications - apptypes are alphanum - use "all" to get all notifications, prefix with `-` to exclude (eg. 'all,-talk')
	 * @return DataResponse<Http::STATUS_OK|Http::STATUS_CREATED|Http::STATUS_UNAUTHORIZED, list<empty>, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array{}>
	 *
	 * 200: A subscription was already registered and activated
	 * 201: New subscription registered successfully
	 * 400: Registering is not possible
	 * 401: Missing permissions to register
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion}/webpush', requirements: ['apiVersion' => '(v2)'])]
	public function registerWP(string $endpoint, string $uaPublicKey, string $auth, string $apptypes): DataResponse {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse([], Http::STATUS_UNAUTHORIZED);
		}

		if (!WebPushClient::isValidP256dh($uaPublicKey)) {
			return new DataResponse(['message' => 'INVALID_P256DH'], Http::STATUS_BAD_REQUEST);
		}

		if (!WebPushClient::isValidAuth($auth)) {
			return new DataResponse(['message' => 'INVALID_AUTH'], Http::STATUS_BAD_REQUEST);
		}

		if (
			!filter_var($endpoint, FILTER_VALIDATE_URL)
			|| \strlen($endpoint) > 1000
			|| !preg_match('/^https\:\/\//', $endpoint)
		) {
			return new DataResponse(['message' => 'INVALID_ENDPOINT'], Http::STATUS_BAD_REQUEST);
		}

		if (strlen($apptypes) > 256) {
			return new DataResponse(['message' => 'TOO_MANY_APP_TYPES'], Http::STATUS_BAD_REQUEST);
		}

		$tokenId = $this->session->get('token-id');
		if (!\is_int($tokenId)) {
			return new DataResponse(['message' => 'INVALID_SESSION_TOKEN'], Http::STATUS_BAD_REQUEST);
		}
		try {
			$token = $this->tokenProvider->getTokenById($tokenId);
		} catch (InvalidTokenException) {
			return new DataResponse(['message' => 'INVALID_SESSION_TOKEN'], Http::STATUS_BAD_REQUEST);
		}

		[$status, $activationToken] = $this->saveSubscription($user, $token, $endpoint, $uaPublicKey, $auth, $apptypes);

		if ($status === NewSubStatus::CREATED) {
			$wp = $this->getWPClient();
			$wp->notify($endpoint, $uaPublicKey, $auth, json_encode(['activationToken' => $activationToken]));
		}

		return match($status) {
			NewSubStatus::UPDATED => new DataResponse([], Http::STATUS_OK),
			NewSubStatus::CREATED => new DataResponse([], Http::STATUS_CREATED),
			// This should not happen
			NewSubStatus::ERROR => new DataResponse(['message' => 'DB_ERROR'], Http::STATUS_BAD_REQUEST),
		};
	}

	/**
	 * Activate subscription for push notifications
	 *
     * @param string $activationToken Random token sent via a push notification during registration to enable the subscription
	 * @return DataResponse<Http::STATUS_OK|Http::STATUS_ACCEPTED|Http::STATUS_UNAUTHORIZED, list<empty>, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array{}>
	 *
	 * 200: Subscription was already activated
	 * 202: Subscription activated successfully
	 * 400: Activating subscription is not possible, may be because of a wrong activation token
	 * 401: Missing permissions to activate subscription
	 * 404: No subscription found for the device
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion}/webpush/activate', requirements: ['apiVersion' => '(v2)'])]
	public function activateWP(string $activationToken): DataResponse {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse([], Http::STATUS_UNAUTHORIZED);
		}

		$tokenId = (int)$this->session->get('token-id');
		try {
			$token = $this->tokenProvider->getTokenById($tokenId);
		} catch (InvalidTokenException) {
			return new DataResponse(['message' => 'INVALID_SESSION_TOKEN'], Http::STATUS_BAD_REQUEST);
		}

		$status = $this->activateSubscription($user, $token, $activationToken);

		return match($status) {
			ActivationSubStatus::OK => new DataResponse([], Http::STATUS_OK),
			ActivationSubStatus::CREATED => new DataResponse([], Http::STATUS_ACCEPTED),
			ActivationSubStatus::NO_TOKEN => new DataResponse(['message' => 'INVALID_ACTIVATION_TOKEN'], Http::STATUS_BAD_REQUEST),
			ActivationSubStatus::NO_SUB => new DataResponse(['message' => 'NO_PUSH_SUBSCRIPTION'], Http::STATUS_NOT_FOUND),
		};
	}

	/**
	 * Remove a subscription from push notifications
	 *
	 * @return DataResponse<Http::STATUS_OK|Http::STATUS_ACCEPTED|Http::STATUS_UNAUTHORIZED, list<empty>, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array{}>
	 *
	 * 200: No subscription for the device
	 * 202: Subscription removed successfully
	 * 400: Removing subscription is not possible
	 * 401: Missing permissions to remove subscription
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/{apiVersion}/webpush', requirements: ['apiVersion' => '(v2)'])]
	public function removeWP(): DataResponse {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse([], Http::STATUS_UNAUTHORIZED);
		}

		$tokenId = (int)$this->session->get('token-id');
		try {
			$token = $this->tokenProvider->getTokenById($tokenId);
		} catch (InvalidTokenException) {
			return new DataResponse(['message' => 'INVALID_SESSION_TOKEN'], Http::STATUS_BAD_REQUEST);
		}

		if ($this->deleteSubscription($user, $token)) {
			return new DataResponse([], Http::STATUS_ACCEPTED);
		}

		return new DataResponse([], Http::STATUS_OK);
	}

	protected function getWPClient(): WebPushClient {
		return new WebPushClient($this->appConfig);
	}

	/**
	 * @param string $apptypes comma separated list of types
	 * @return array{0: NewSubStatus, 1: ?string}:
	 *     - CREATED if the user didn't have an activated subscription with this endpoint, pubkey and auth
	 *     - UPDATED if the subscription has been updated (use to change apptypes)
	 */
	protected function saveSubscription(IUser $user, IToken $token, string $endpoint, string $uaPublicKey, string $auth, string $apptypes): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from('notifications_webpush')
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId())))
			->andWhere($query->expr()->eq('endpoint', $query->createNamedParameter($endpoint)))
			->andWhere($query->expr()->eq('p256dh', $query->createNamedParameter($uaPublicKey)))
			->andWhere($query->expr()->eq('auth', $query->createNamedParameter($auth)))
			->andWhere($query->expr()->eq('activated', $query->createNamedParameter(true)));
		$result = $query->executeQuery();
		$row = $result->fetch();
		$result->closeCursor();

		if (!$row) {
			// In case the user has already a subscription, but inactive or with a different enpoint, pubkey or auth secret
			$this->deleteSubscription($user, $token);
			$activationToken = Uuid::v4()->toRfc4122();
			if ($this->insertSubscription($user, $token, $endpoint, $uaPublicKey, $auth, $activationToken, $apptypes)) {
				return [NewSubStatus::CREATED, $activationToken];
			} else {
				return [NewSubStatus::ERROR, null];
			}
		}

		if ($this->updateSubscription($user, $token, $endpoint, $uaPublicKey, $auth, $apptypes)) {
			return [NewSubStatus::UPDATED, null];
		} else {
			return [NewSubStatus::ERROR, null];
		}
	}

	/**
	 * @return ActivationSubStatus:
	 *     - OK if it was already activated
	 *     - CREATED If the entry was updated
	 *     - NO_TOKEN if we don't have this token
	 *     - NO_SUB if we don't have this subscription
	 */
	protected function activateSubscription(IUser $user, IToken $token, string $activationToken): ActivationSubStatus {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from('notifications_webpush')
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId())));
		$result = $query->executeQuery();
		$row = $result->fetch();
		$result->closeCursor();

		if (!$row) {
			return ActivationSubStatus::NO_SUB;
		}
		if ($row['activated']) {
			return ActivationSubStatus::OK;
		}
		$query->update('notifications_webpush')
			->set('activated', $query->createNamedParameter(true))
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT)))
			->andWhere($query->expr()->eq('activation_token', $query->createNamedParameter($activationToken)));

		if ($query->executeStatement() !== 0) {
			return ActivationSubStatus::CREATED;
		} else {
			return ActivationSubStatus::NO_TOKEN;
		}
	}

	/**
	 * @param string $apptypes comma separated list of types
	 * @return bool If the entry was created
	 */
	protected function insertSubscription(IUser $user, IToken $token, string $endpoint, string $uaPublicKey, string $auth, string $activationToken, string $apptypes): bool {
		$query = $this->db->getQueryBuilder();
		$query->insert('notifications_webpush')
			->values([
				'uid' => $query->createNamedParameter($user->getUID()),
				'token' => $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT),
				'endpoint' => $query->createNamedParameter($endpoint),
				'p256dh' => $query->createNamedParameter($uaPublicKey),
				'auth' => $query->createNamedParameter($auth),
				'apptypes' => $query->createNamedParameter($apptypes),
				'activation_token' => $query->createNamedParameter($activationToken),
			]);
		return $query->executeStatement() > 0;
	}

	/**
	 * @param string $apptypes comma separated list of types
	 * @return bool If the entry was updated
	 */
	protected function updateSubscription(IUser $user, IToken $token, string $endpoint, string $uaPublicKey, string $auth, string $apptypes): bool {
		$query = $this->db->getQueryBuilder();
		$query->update('notifications_webpush')
			->set('endpoint', $query->createNamedParameter($endpoint))
			->set('p256dh', $query->createNamedParameter($uaPublicKey))
			->set('auth', $query->createNamedParameter($auth))
			->set('apptypes', $query->createNamedParameter($apptypes))
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT)));

		return $query->executeStatement() !== 0;
	}

	/**
	 * @return bool If the entry was deleted
	 */
	protected function deleteSubscription(IUser $user, IToken $token): bool {
		$query = $this->db->getQueryBuilder();
		$query->delete('notifications_webpush')
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT)));

		return $query->executeStatement() !== 0;
	}
}
