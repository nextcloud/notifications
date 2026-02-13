<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit\Controller;

use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\Authentication\Token\IToken;
use OC\Security\IdentityProof\Manager;
use OCA\Notifications\Controller\ActivationSubStatus;
use OCA\Notifications\Controller\NewSubStatus;
use OCA\Notifications\Controller\WebPushController;
use OCA\Notifications\WebPushClient;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IAppConfig;
use OCP\IDBConnection;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class WebPushControllerTest extends TestCase {
	protected IRequest&MockObject $request;
	protected IAppConfig&MockObject $appConfig;
	protected IDBConnection&MockObject $db;
	protected ISession&MockObject $session;
	protected IUserSession&MockObject $userSession;
	protected IProvider&MockObject $tokenProvider;
	protected Manager&MockObject $identityProof;
	protected IUser&MockObject $user;
	protected WebPushController $controller;

	// They are testing values, do not use in production
	protected static string $uaPublicKey = 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcxaOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4';
	protected static string $auth = 'BTBZMqHH6r4Tts7J_aSIgg';

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->db = $this->createMock(IDBConnection::class);
		$this->session = $this->createMock(ISession::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->tokenProvider = $this->createMock(IProvider::class);
		$this->identityProof = $this->createMock(Manager::class);
	}

	protected function getController(array $methods = []): WebPushController|MockObject {
		if (empty($methods)) {
			return new WebPushController(
				'notifications',
				$this->request,
				$this->appConfig,
				$this->db,
				$this->session,
				$this->userSession,
				$this->tokenProvider,
				$this->identityProof
			);
		}

		return $this->getMockBuilder(WebPushController::class)
			->setConstructorArgs([
				'notifications',
				$this->request,
				$this->appConfig,
				$this->db,
				$this->session,
				$this->userSession,
				$this->tokenProvider,
				$this->identityProof,
			])
			->onlyMethods($methods)
			->getMock();
	}

	public static function dataRegisterWP(): array {
		return [
			'not authenticated' => [
				'https://localhost/',
				'',
				'',
				'all',
				false,
				0,
				false,
				0,
				[],
				Http::STATUS_UNAUTHORIZED
			],
			'too short uaPubKey' => [
				'https://localhost/',
				'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV',
				self::$auth,
				'all',
				true,
				0,
				false,
				0,
				['message' => 'INVALID_P256DH'],
				Http::STATUS_BAD_REQUEST,
			],
			'too long uaPubKey' => [
				'https://localhost/',
				'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcxaOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4bb',
				self::$auth,
				'all',
				true,
				0,
				false,
				0,
				['message' => 'INVALID_P256DH'],
				Http::STATUS_BAD_REQUEST,
			],
			'invalid char in uaPubKey' => [
				'https://localhost/',
				'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV- JvLexhqUzORcxaOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw',
				self::$auth,
				'all',
				true,
				0,
				false,
				0,
				['message' => 'INVALID_P256DH'],
				Http::STATUS_BAD_REQUEST,
			],
			'too short auth' => [
				'https://localhost/',
				self::$uaPublicKey,
				'BTBZMqHH6r4Tts7J_aSI',
				'all',
				true,
				0,
				false,
				0,
				['message' => 'INVALID_AUTH'],
				Http::STATUS_BAD_REQUEST,
			],
			'too long auth' => [
				'https://localhost/',
				self::$uaPublicKey,
				'BTBZMqHH6r4Tts7J_aSIggxx',
				'all',
				true,
				0,
				false,
				0,
				['message' => 'INVALID_AUTH'],
				Http::STATUS_BAD_REQUEST,
			],
			'invalid char in auth' => [
				'https://localhost/',
				self::$uaPublicKey,
				'BTBZM HH6r4Tts7J_aSIgg',
				'all',
				true,
				0,
				false,
				0,
				['message' => 'INVALID_AUTH'],
				Http::STATUS_BAD_REQUEST,
			],
			'invalid endpoint' => [
				'http://localhost/',
				self::$uaPublicKey,
				self::$auth,
				'all',
				true,
				0,
				false,
				0,
				['message' => 'INVALID_ENDPOINT'],
				Http::STATUS_BAD_REQUEST,
			],
			'too many app_types' => [
				'https://localhost/',
				self::$uaPublicKey,
				self::$auth,
				'all,aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
				true,
				0,
				false,
				0,
				['message' => 'TOO_MANY_APP_TYPES'],
				Http::STATUS_BAD_REQUEST,
			],
			'invalid session' => [
				'https://localhost/',
				self::$uaPublicKey,
				self::$auth,
				'all',
				true,
				23,
				false,
				0,
				['message' => 'INVALID_SESSION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'created' => [
				'https://localhost/',
				self::$uaPublicKey,
				self::$auth,
				'all',
				true,
				23,
				true,
				0,
				[],
				Http::STATUS_CREATED,
			],
			'updated' => [
				'https://localhost/',
				self::$uaPublicKey,
				self::$auth,
				'all',
				true,
				23,
				true,
				1,
				[],
				Http::STATUS_OK,
			],
		];
	}

	#[DataProvider(methodName: 'dataRegisterWP')]
	public function testRegisterWP(string $endpoint, string $uaPublicKey, string $auth, string $appTypes, bool $userIsValid, int $tokenId, bool $tokenIsValid, int $subStatus, array $payload, int $status): void {
		$controller = $this->getController([
			'saveSubscription',
			'getWPClient'
		]);

		$user = $this->createMock(IUser::class);
		if ($userIsValid) {
			$this->userSession->expects($this->any())
				->method('getUser')
				->willReturn($user);
		} else {
			$this->userSession->expects($this->any())
				->method('getUser')
				->willReturn(null);
		}

		$this->session->expects($tokenId > 0 ? $this->once() : $this->never())
			->method('get')
			->with('token-id')
			->willReturn($tokenId);

		if ($tokenIsValid) {
			$token = $this->createMock(IToken::class);
			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willReturn($token);

			$controller->expects($this->once())
				->method('saveSubscription')
				->with($user, $token, $endpoint, $uaPublicKey, $auth, $this->anything())
				->willReturn([NewSubStatus::from($subStatus), 'tok']);

			if ($subStatus === 0) {
				$wpClient = $this->createMock(WebPushClient::class);
				$controller->expects($this->once())
					->method('getWPClient')
					->willReturn($wpClient);
			}
		} else {
			$controller->expects($this->never())
				->method('saveSubscription');

			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willThrowException(new InvalidTokenException());
		}

		$response = $controller->registerWP($endpoint, $uaPublicKey, $auth, $appTypes);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame($status, $response->getStatus());
		$this->assertSame($payload, $response->getData());
	}

	public static function dataActivateWP(): array {
		return [
			'not authenticated' => [
				false,
				0,
				false,
				0,
				[],
				Http::STATUS_UNAUTHORIZED
			],
			'invalid session token' => [
				true,
				23,
				false,
				0,
				['message' => 'INVALID_SESSION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'created' => [
				true,
				23,
				true,
				0,
				[],
				Http::STATUS_ACCEPTED,
			],
			'updated' => [
				true,
				42,
				true,
				1,
				[],
				Http::STATUS_OK,
			],
			'invalid activation token' => [
				true,
				42,
				true,
				2,
				['message' => 'INVALID_ACTIVATION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'no subscription' => [
				true,
				42,
				true,
				3,
				['message' => 'NO_PUSH_SUBSCRIPTION'],
				Http::STATUS_NOT_FOUND,
			],
		];
	}

	#[DataProvider(methodName: 'dataActivateWP')]
	public function testActivateWP(bool $userIsValid, int $tokenId, bool $tokenIsValid, int $subStatus, array $payload, int $status): void {
		$controller = $this->getController([
			'activateSubscription',
		]);

		$user = $this->createMock(IUser::class);
		if ($userIsValid) {
			$this->userSession->expects($this->any())
				->method('getUser')
				->willReturn($user);
		} else {
			$this->userSession->expects($this->any())
				->method('getUser')
				->willReturn(null);
		}

		$this->session->expects($tokenId > 0 ? $this->once() : $this->never())
			->method('get')
			->with('token-id')
			->willReturn($tokenId);

		if ($tokenIsValid) {
			$token = $this->createMock(IToken::class);
			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willReturn($token);

			$controller->expects($this->once())
				->method('activateSubscription')
				->with($user, $token, 'dummyToken')
				->willReturn(ActivationSubStatus::from($subStatus));
		} else {
			$controller->expects($this->never())
				->method('activateSubscription');

			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willThrowException(new InvalidTokenException());
		}

		$response = $controller->activateWP('dummyToken');
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame($status, $response->getStatus());
		$this->assertSame($payload, $response->getData());
	}

	public static function dataRemoveSubscription(): array {
		return [
			'not authenticated' => [
				false,
				0,
				false,
				null,
				[],
				Http::STATUS_UNAUTHORIZED
			],
			'invalid session token' => [
				true,
				23,
				false,
				null,
				['message' => 'INVALID_SESSION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'subscription deleted' => [
				true,
				23,
				true,
				true,
				[],
				Http::STATUS_ACCEPTED,
			],
			'subscription non existent' => [
				true,
				42,
				true,
				false,
				[],
				Http::STATUS_OK,
			],
		];
	}

	#[DataProvider(methodName: 'dataRemoveSubscription')]
	public function testRemoveSubscription(bool $userIsValid, int $tokenId, bool $tokenIsValid, ?bool $subDeleted, array $payload, int $status): void {
		$controller = $this->getController([
			'deleteSubscription',
		]);

		$user = $this->createMock(IUser::class);
		if ($userIsValid) {
			$this->userSession->expects($this->any())
				->method('getUser')
				->willReturn($user);
		} else {
			$this->userSession->expects($this->any())
				->method('getUser')
				->willReturn(null);
		}

		$this->session->expects($tokenId > 0 ? $this->once() : $this->never())
			->method('get')
			->with('token-id')
			->willReturn($tokenId);

		if ($tokenIsValid) {
			$token = $this->createMock(IToken::class);
			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willReturn($token);

			$controller->expects($this->once())
				->method('deleteSubscription')
				->with($user, $token)
				->willReturn($subDeleted);
		} else {
			$controller->expects($this->never())
				->method('deleteSubscription');

			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willThrowException(new InvalidTokenException());
		}

		$response = $controller->removeWP();
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame($status, $response->getStatus());
		$this->assertSame($payload, $response->getData());
	}
}
