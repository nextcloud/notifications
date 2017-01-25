<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Notifications\Controller;

use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\Authentication\Token\IToken;
use OC\Security\IdentityProof\Crypto;
use OC\Security\IdentityProof\Manager;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\OCSController;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserSession;

class PushController extends OCSController {

	/** @var IDBConnection */
	private $db;

	/** @var ISession */
	private $session;

	/** @var IUserSession */
	private $userSession;

	/** @var IProvider */
	private $tokenProvider;

	/** @var Manager */
	private $identityProof;

	/** @var Crypto */
	private $crypto;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IDBConnection $db
	 * @param ISession $session
	 * @param IUserSession $userSession
	 * @param IProvider $tokenProvider
	 * @param Manager $identityProof
	 * @param Crypto $crypto
	 */
	public function __construct($appName, IRequest $request, IDBConnection $db, ISession $session, IUserSession $userSession, IProvider $tokenProvider, Manager $identityProof, Crypto $crypto) {
		parent::__construct($appName, $request);

		$this->db = $db;
		$this->session = $session;
		$this->userSession = $userSession;
		$this->tokenProvider = $tokenProvider;
		$this->identityProof = $identityProof;
		$this->crypto = $crypto;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $pushTokenHash
	 * @param string $devicePublicKey
	 * @return JSONResponse
	 */
	public function registerDevice($pushTokenHash, $devicePublicKey) {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new JSONResponse([], Http::STATUS_UNAUTHORIZED);
		}

		if (!preg_match('/^([a-f0-9]{128})$/', $pushTokenHash)) {
			return new JSONResponse(['message' => 'Invalid hashed push token'], Http::STATUS_BAD_REQUEST);
		}

		if (strlen($devicePublicKey) !== 450 ||
			strpos($devicePublicKey, '-----BEGIN PUBLIC KEY-----') !== 0 ||
			strpos($devicePublicKey, '-----END PUBLIC KEY-----') !== 426) {
			return new JSONResponse(['message' => 'Invalid device public key'], Http::STATUS_BAD_REQUEST);
		}

		$sessionId = $this->session->getId();
		try {
			$token = $this->tokenProvider->getToken($sessionId);
		} catch (InvalidTokenException $e) {
			return new JSONResponse(['message' => 'Could not identify session token'], Http::STATUS_BAD_REQUEST);
		}

		$key = $this->identityProof->getKey($user);

		$created = $this->savePushToken($user, $token, $devicePublicKey, $pushTokenHash);

		$encryptedData = $this->crypto->encrypt(json_encode([$user->getCloudId(), $token->getId()]), $user);
		return new JSONResponse([
			'publicKey' => $key->getPublic(),
			'deviceIdentifier' => $encryptedData['message'],
			'signature' => $encryptedData['signature'],
		], $created ? Http::STATUS_CREATED : Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $devicePublicKey
	 * @return JSONResponse
	 */
	public function removeDevice($devicePublicKey) {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new JSONResponse([], Http::STATUS_UNAUTHORIZED);
		}

		if (strlen($devicePublicKey) !== 450 ||
			strpos($devicePublicKey, '-----BEGIN PUBLIC KEY-----') !== 0 ||
			strpos($devicePublicKey, '-----END PUBLIC KEY-----') !== 425) {
			return new JSONResponse(['message' => 'Invalid device public key'], Http::STATUS_BAD_REQUEST);
		}

		$sessionId = $this->session->getId();
		try {
			$token = $this->tokenProvider->getToken($sessionId);
		} catch (InvalidTokenException $e) {
			return new JSONResponse(['message' => 'Could not identify session token'], Http::STATUS_BAD_REQUEST);
		}

		$this->deletePushToken($user, $token, $devicePublicKey);
		return new JSONResponse();
	}

	/**
	 * @param IUser $user
	 * @param IToken $token
	 * @param string $devicePublicKey
	 * @param string $pushTokenHash
	 * @return bool If the hash was new to the database
	 */
	protected function savePushToken(IUser $user, IToken $token, $devicePublicKey, $pushTokenHash) {
		$query = $this->db->getQueryBuilder();
		$query->select('pushtokenhash')
			->from('notifications_pushtokens')
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId())))
			->andWhere($query->expr()->eq('devicepublickey', $query->createNamedParameter($devicePublicKey)));
		$result = $query->execute();
		$row = $result->fetch();
		$result->closeCursor();

		if (!$row) {
			return $this->insertPushToken($user, $token, $devicePublicKey, $pushTokenHash);
		} else if ($row['pushtokenhash'] !== $pushTokenHash) {
			return $this->updatePushToken($user, $token, $devicePublicKey, $pushTokenHash);
		}
		return false;
	}

	/**
	 * @param IUser $user
	 * @param IToken $token
	 * @param string $devicePublicKey
	 * @param string $pushTokenHash
	 * @return bool If the entry was created
	 */
	protected function insertPushToken(IUser $user, IToken $token, $devicePublicKey, $pushTokenHash) {
		$query = $this->db->getQueryBuilder();
		$query->insert('notifications_pushtokens')
			->values([
				'uid' => $query->createNamedParameter($user->getUID()),
				'token' => $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT),
				'devicepublickey' => $query->createNamedParameter($devicePublicKey),
				'pushtokenhash' => $query->createNamedParameter($pushTokenHash),
			]);
		return $query->execute() > 0;
	}

	/**
	 * @param IUser $user
	 * @param IToken $token
	 * @param string $devicePublicKey
	 * @param string $pushTokenHash
	 * @return bool If the entry was updated
	 */
	protected function updatePushToken(IUser $user, IToken $token, $devicePublicKey, $pushTokenHash) {
		$query = $this->db->getQueryBuilder();
		$query->update('notifications_pushtokens')
			->set('pushtokenhash', $query->createNamedParameter($pushTokenHash))
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT)))
			->andWhere($query->expr()->eq('devicepublickey', $query->createNamedParameter($devicePublicKey)));
		return $query->execute() > 0;
	}

	/**
	 * @param IUser $user
	 * @param IToken $token
	 * @param string $devicePublicKey
	 * @return bool If the entry was deleted
	 */
	protected function deletePushToken(IUser $user, IToken $token, $devicePublicKey) {
		$query = $this->db->getQueryBuilder();
		$query->delete('notifications_pushtokens')
			->where($query->expr()->eq('uid', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('token', $query->createNamedParameter($token->getId(), IQueryBuilder::PARAM_INT)))
			->andWhere($query->expr()->eq('devicepublickey', $query->createNamedParameter($devicePublicKey)));
		return $query->execute() > 0;
	}
}
