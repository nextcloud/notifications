<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Controller;

use OC\Authentication\Token\IProvider;
use OC\Security\IdentityProof\Manager;
use OCA\Notifications\ResponseDefinitions;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\Authentication\Exceptions\InvalidTokenException;
use OCP\Authentication\Token\IToken;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserSession;

/**
 * @psalm-import-type NotificationsPushDevice from ResponseDefinitions
 */
#[OpenAPI(scope: 'push')]
class PushController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		protected IDBConnection $db,
		protected ISession $session,
		protected IUserSession $userSession,
		protected IProvider $tokenProvider,
		protected Manager $identityProof,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Register device for push notifications
	 *
	 * @param string $pushTokenHash Hash of the push token
	 * @param string $devicePublicKey Public key of the device
	 * @param string $proxyServer Proxy server to be used
	 * @return DataResponse<Http::STATUS_OK|Http::STATUS_CREATED, NotificationsPushDevice, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array{}>|DataResponse<Http::STATUS_UNAUTHORIZED, list<empty>, array{}>
	 *
	 * 200: Device was already registered
	 * 201: Device registered successfully
	 * 400: Registering device is not possible
	 * 401: Missing permissions to register device
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion}/push', requirements: ['apiVersion' => '(v2)'])]
	public function registerDevice(string $pushTokenHash, string $devicePublicKey, string $proxyServer): DataResponse {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse([], Http::STATUS_UNAUTHORIZED);
		}

		if (!preg_match('/^([a-f0-9]{128})$/', $pushTokenHash)) {
			return new DataResponse(['message' => 'INVALID_PUSHTOKEN_HASH'], Http::STATUS_BAD_REQUEST);
		}

		if (
			!str_starts_with($devicePublicKey, '-----BEGIN PUBLIC KEY-----' . "\n")
			|| ((\strlen($devicePublicKey) !== 450 || strpos($devicePublicKey, "\n" . '-----END PUBLIC KEY-----') !== 425)
				&& (\strlen($devicePublicKey) !== 451 || strpos($devicePublicKey, "\n" . '-----END PUBLIC KEY-----' . "\n") !== 425))
		) {
			return new DataResponse(['message' => 'INVALID_DEVICE_KEY'], Http::STATUS_BAD_REQUEST);
		}

		if (
			!filter_var($proxyServer, FILTER_VALIDATE_URL)
			|| \strlen($proxyServer) > 256
			|| !preg_match('/^(https\:\/\/|http\:\/\/(localhost|[a-z0-9\.-]*\.(internal|local))(\:\d{0,5})?\/)/', $proxyServer)
		) {
			return new DataResponse(['message' => 'INVALID_PROXY_SERVER'], Http::STATUS_BAD_REQUEST);
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

		$key = $this->identityProof->getKey($user);

		try {
			$deviceIdentifier = json_encode([$user->getCloudId(), $token->getId()], JSON_THROW_ON_ERROR);
		} catch (\JsonException) {
			return new DataResponse(['message' => 'INVALID_SESSION_TOKEN'], Http::STATUS_BAD_REQUEST);
		}
		openssl_sign($deviceIdentifier, $signature, $key->getPrivate(), OPENSSL_ALGO_SHA512);
		/**
		 * For some reason the push proxy's golang code needs the signature
		 * of the deviceIdentifier before the sha512 hashing. Assumption is that
		 * openssl_sign already does the sha512 internally.
		 */
		$deviceIdentifier = base64_encode(hash('sha512', $deviceIdentifier, true));

		$appType = 'unknown';
		if ($this->request->isUserAgent([
			IRequest::USER_AGENT_TALK_ANDROID,
			IRequest::USER_AGENT_TALK_IOS,
		])) {
			$appType = 'talk';
		} elseif ($this->request->isUserAgent([
			IRequest::USER_AGENT_CLIENT_ANDROID,
			IRequest::USER_AGENT_CLIENT_IOS,
		])) {
			$appType = 'nextcloud';
		}

		$created = $this->savePushToken($user, $token, $deviceIdentifier, $devicePublicKey, $pushTokenHash, $proxyServer, $appType);

		return new DataResponse([
			'publicKey' => $key->getPublic(),
			'deviceIdentifier' => $deviceIdentifier,
			'signature' => base64_encode($signature),
		], $created ? Http::STATUS_CREATED : Http::STATUS_OK);
	}

	/**
	 * Remove a device from push notifications
	 *
	 * @return DataResponse<Http::STATUS_OK|Http::STATUS_ACCEPTED|Http::STATUS_UNAUTHORIZED, list<empty>, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array{}>
	 *
	 * 200: No device registered
	 * 202: Device removed successfully
	 * 400: Removing device is not possible
	 * 401: Missing permissions to remove device
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/{apiVersion}/push', requirements: ['apiVersion' => '(v2)'])]
	public function removeDevice(): DataResponse {
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

		if ($this->deletePushToken($user, $token)) {
			return new DataResponse([], Http::STATUS_ACCEPTED);
		}

		return new DataResponse([], Http::STATUS_OK);
	}

	/**
	 * @return bool If the hash was new to the database
	 */
	protected function savePushToken(IUser $user, IToken $token, string $deviceIdentifier, string $devicePublicKey, string $pushTokenHash, string $proxyServer, string $appType): bool {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from('notifications_pushhash')
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId())));
		$result = $query->executeQuery();
		$row = $result->fetch();
		$result->closeCursor();

		if (!$row) {
			// In case the auth token is new, delete potentially old entries for the same device (push token) by this user
			$this->deletePushTokenByHash($user, $pushTokenHash);

			return $this->insertPushToken($user, $token, $deviceIdentifier, $devicePublicKey, $pushTokenHash, $proxyServer, $appType);
		}

		return $this->updatePushToken($user, $token, $devicePublicKey, $pushTokenHash, $proxyServer, $appType);
	}

	/**
	 * @return bool If the entry was created
	 */
	protected function insertPushToken(IUser $user, IToken $token, string $deviceIdentifier, string $devicePublicKey, string $pushTokenHash, string $proxyServer, string $appType): bool {
		$devicePublicKeyHash = hash('sha512', $devicePublicKey);

		$query = $this->db->getQueryBuilder();
		$query->insert('notifications_pushhash')
			->values([
				'uid' => $query->createNamedParameter($user->getUID()),
				'token' => $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT),
				'deviceidentifier' => $query->createNamedParameter($deviceIdentifier),
				'devicepublickey' => $query->createNamedParameter($devicePublicKey),
				'devicepublickeyhash' => $query->createNamedParameter($devicePublicKeyHash),
				'pushtokenhash' => $query->createNamedParameter($pushTokenHash),
				'proxyserver' => $query->createNamedParameter($proxyServer),
				'apptype' => $query->createNamedParameter($appType),
			]);
		return $query->executeStatement() > 0;
	}

	/**
	 * @return bool If the entry was updated
	 */
	protected function updatePushToken(IUser $user, IToken $token, string $devicePublicKey, string $pushTokenHash, string $proxyServer, string $appType): bool {
		$devicePublicKeyHash = hash('sha512', $devicePublicKey);

		$query = $this->db->getQueryBuilder();
		$query->update('notifications_pushhash')
			->set('devicepublickey', $query->createNamedParameter($devicePublicKey))
			->set('devicepublickeyhash', $query->createNamedParameter($devicePublicKeyHash))
			->set('pushtokenhash', $query->createNamedParameter($pushTokenHash))
			->set('proxyserver', $query->createNamedParameter($proxyServer))
			->set('apptype', $query->createNamedParameter($appType))
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT)));

		return $query->executeStatement() !== 0;
	}

	/**
	 * @return bool If the entry was deleted
	 */
	protected function deletePushToken(IUser $user, IToken $token): bool {
		$query = $this->db->getQueryBuilder();
		$query->delete('notifications_pushhash')
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT)));

		return $query->executeStatement() !== 0;
	}

	/**
	 * @return bool If the entry was deleted
	 */
	protected function deletePushTokenByHash(IUser $user, string $pushTokenHash): bool {
		$query = $this->db->getQueryBuilder();
		$query->delete('notifications_pushhash')
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('pushtokenhash', $query->createNamedParameter($pushTokenHash)));

		return $query->executeStatement() !== 0;
	}
}
