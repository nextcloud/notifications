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
use OC\Security\IdentityProof\Key;
use OC\Security\IdentityProof\Manager;
use OCA\Notifications\Controller\PushController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IDBConnection;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class PushControllerTest extends TestCase {
	protected IRequest&MockObject $request;
	protected IDBConnection&MockObject $db;
	protected ISession&MockObject $session;
	protected IUserSession&MockObject $userSession;
	protected IProvider&MockObject $tokenProvider;
	protected Manager&MockObject $identityProof;
	protected IUser&MockObject $user;
	protected PushController $controller;

	protected static string $devicePublicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA2Or1KumSDfk8dT0MuCW9
WS5wkVOpNsbz2OIJFBYrBvu6joC2iQo9StONMaXoTQj5Ucak9UBtC60PHyTkIDFb
HOpCST5onmIAtZdqHN/3ABOBeHVU/notdRIl/menGM64jiqGWvE06F1+yZ8GGcGQ
8RKzabqMd2K1iUohXP625uzTABVaiwz3u8nGEwui5R6Pf5Fy6DccuqdUMtJIfW21
Z4Tj48Tw+pR+fUrGpa1Wg+wiwlg7ISK8Symml1Rd6hSRXK2t8Opm/kjH9ZX8oVwn
RSO1ehjzRpTY+gdw/5gvwMZI0XmrIanZmZHwePRR4HC6FLPrL2OQG3gWikDIPyTS
hQIDAQAB
-----END PUBLIC KEY-----';

	protected static string $userPrivateKey = '-----BEGIN PRIVATE KEY-----
MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDPR0uV6e1cNSoy
vsITBvGyYpOIn9vI7zpEhk7FGGwdOTd2dxxJ2ikegRJ6Fr2Ojce15K3zfiasXPen
TAQuFEXecGoP9WY+DS5X1LfCpj9EeAOBfVGKeQDst5z/GoXeU+YqWbayJTp6vFRj
7o5X6QDCCXy25Kt4snNDWTHPlMc44BLjZ6w+Wj0D2ySlz1dGpunc0vwYN/uEyjr9
ztmiN82TZtZHgzN43DJSv7tLufsZgGsWnVlytXmsi4QuCAKcm92X2ZtIXkn5niMW
DxJJepqFx7pC3ILXMZKYolAtt91VvLiGQjzURhq7HA4QdqvFyKXp0uLN2rKZjqQ0
2nUzC34XAgMBAAECggEAFrL/Ew7IIKXt1hrP1BeZlmh3MaoX/pw8LE7tB2aSSG0A
pueKYIgUorON23LsFVVvfnrpldXF1HBl6ptHhehQcnirFM5SAQ+eeJ3h9d4Q5aWi
9KZNrLVtpX7CIam86UkU1qR2fnHXQqOnNj5ktjndDGLPlpPaN2CLgN+etdXcL10g
G5fltrFnTzYgkYap/eNkY+ivA+0xqc1l3jP2i5PHihv1adcoiOuam36GARM9C51X
fyWvMtxMvkRAZsdTATtRcQsEoJuQ3Rvseei38forkQdRn9p61UW8VT6Wa/+DWebO
Ll4OAv1RH4H2V6nrYY2ILJNnPzP8V4hjP9OGEAUQ8QKBgQDssSBUmb8Ztt6SsHNr
fgnbJBGAYizB1oAr6W1kLTQCq+BYirSYWMcJ/rakx+VCPmZ1fbbGYjPX5yVUsskx
jQ/GUT7D8lMIQNZiI9CqWR0+fJpVJ/zxwrPT2jqu8lEJxq2i/WB0nRHCgosGBTmw
UqhRGLkE5Ds14Q0zePZbdpAAyQKBgQDgL+yftcJEam8c3ipkrv02aT7vghoB0pAg
JNSSwhXED1CTboccY4daOfTYdt/PnkVmndENrUGMRyEbAY0DDK6hclG6/gE3fwn4
mL33IIzQ9BCoXxr3tcS0r4iQjbGKorUNJW1OwmkqyMZ4POF9BSkLXpTTcJaM5WxU
8JU9PmLX3wKBgFNpuLMX27j8MUQQ2xwuttp7w48zCgLlzRWsldiP9ZxbZhzOBQcL
glmLYmJ/79OAmisduqP/R7X2x7kpqK3FwKFrUGtNouVttB+x73+ZGC1FTD5mcUXi
D+3BIp002EpRsi+Wi7+M+w1JZCUjAkmZV6f8xndq11MNlNFm96sUBXvBAoGAJ9hc
tgYYARDprrfN0RdI6eLKzMbS2IAUHaJuJadZNv+B0rJSUTlfVSn32oFGRiBbNWHX
RhcFD2mU+LfN2DzozMkEvbdnf/WUUBrVqJagcILwcvx0TpJ/451PKGIGrB0/EJcW
Vmk3R+NnYvdvHElOgjbNPMdF+sTL/EzGOZxc9QECgYBNY4LAAKqrw47p+lcRi31O
X4fhdGWAIFyiUliPDkxzEl8857FbT5c6qhdes3Gyc9tSF1wh0X7lpCDquWXYLP1V
9WNvdon+YMRi9BKpO0SlE07lwFANBpz+wJkhONVJBMzvKbxEnMRPRJ4lWa0VAAGE
j2ZL3j2Nwefj3HrR/AkeFA==
-----END PRIVATE KEY-----
';

	protected static string $userPublicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAz0dLlentXDUqMr7CEwbx
smKTiJ/byO86RIZOxRhsHTk3dnccSdopHoESeha9jo3HteSt834mrFz3p0wELhRF
3nBqD/VmPg0uV9S3wqY/RHgDgX1RinkA7Lec/xqF3lPmKlm2siU6erxUY+6OV+kA
wgl8tuSreLJzQ1kxz5THOOAS42esPlo9A9skpc9XRqbp3NL8GDf7hMo6/c7ZojfN
k2bWR4MzeNwyUr+7S7n7GYBrFp1ZcrV5rIuELggCnJvdl9mbSF5J+Z4jFg8SSXqa
hce6QtyC1zGSmKJQLbfdVby4hkI81EYauxwOEHarxcil6dLizdqymY6kNNp1Mwt+
FwIDAQAB
-----END PUBLIC KEY-----
';


	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->db = $this->createMock(IDBConnection::class);
		$this->session = $this->createMock(ISession::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->tokenProvider = $this->createMock(IProvider::class);
		$this->identityProof = $this->createMock(Manager::class);
	}

	protected function getController(array $methods = []): PushController|MockObject {
		if (empty($methods)) {
			return new PushController(
				'notifications',
				$this->request,
				$this->db,
				$this->session,
				$this->userSession,
				$this->tokenProvider,
				$this->identityProof
			);
		}

		return $this->getMockBuilder(PushController::class)
			->setConstructorArgs([
				'notifications',
				$this->request,
				$this->db,
				$this->session,
				$this->userSession,
				$this->tokenProvider,
				$this->identityProof,
			])
			->onlyMethods($methods)
			->getMock();
	}

	public static function dataRegisterDevice(): array {
		return [
			'not authenticated' => [
				'',
				'',
				'',
				false,
				0,
				false,
				null,
				[],
				Http::STATUS_UNAUTHORIZED
			],
			'too short token hash' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e47',
				'',
				'',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_PUSHTOKEN_HASH'],
				Http::STATUS_BAD_REQUEST,
			],
			'too long token hash' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e4722',
				'',
				'',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_PUSHTOKEN_HASH'],
				Http::STATUS_BAD_REQUEST,
			],
			'invalid char in token hash' => [
				'rb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				'',
				'',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_PUSHTOKEN_HASH'],
				Http::STATUS_BAD_REQUEST,
			],
			'device key invalid start' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				substr(self::$devicePublicKey, 1),
				'',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_DEVICE_KEY'],
				Http::STATUS_BAD_REQUEST,
			],
			'device key invalid end' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				substr(self::$devicePublicKey, 0, -1),
				'',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_DEVICE_KEY'],
				Http::STATUS_BAD_REQUEST,
			],
			'device key too much end' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey . "\n\n",
				'',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_DEVICE_KEY'],
				Http::STATUS_BAD_REQUEST,
			],
			'device key without trailing new line' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey,
				'',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_PROXY_SERVER'],
				Http::STATUS_BAD_REQUEST,
			],
			'device key with trailing new line' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey . "\n",
				'',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_PROXY_SERVER'],
				Http::STATUS_BAD_REQUEST,
			],
			'invalid push proxy' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey,
				'localhost',
				true,
				0,
				false,
				null,
				['message' => 'INVALID_PROXY_SERVER'],
				Http::STATUS_BAD_REQUEST,
			],
			'using localhost' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey,
				'http://localhost/',
				true,
				23,
				false,
				null,
				['message' => 'INVALID_SESSION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'using localhost with port' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey,
				'http://localhost:8088/',
				true,
				23,
				false,
				null,
				['message' => 'INVALID_SESSION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'using production' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey,
				'https://push-notifications.nextcloud.com/',
				true,
				23,
				false,
				null,
				['message' => 'INVALID_SESSION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'created or updated' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey,
				'https://push-notifications.nextcloud.com/',
				true,
				23,
				true,
				true,
				[
					'publicKey' => self::$userPublicKey,
					'deviceIdentifier' => 'XUCEZ1EHvTUcVhIvrQQQ1XcP0ZD2BFdFqw4EYbOhBfiEgXgirurR4x/ve4GSSyfivvbQOdOkZUM+g4m+tSb0Ew==',
					'signature' => 'LRhbXO71WYX9qqDbQX7C+87YaaFfWoT/vG0DlaXdBz6+lhyOA0dw/1Ggz3fd7RerCQ0MfgnnTyxO+cSeRpUaPdA2yPjfoiPpfYA5SOJQGF3comS/HYna3fHiFDbOoM3BJOnjvqiSZdxA/ICdyl2mEEC5wO7AZ4OZKBTa5XfL7eSCXZLEv1YldqcLOStbXrI7voDQocTMJxoQZI/j8BVcf2i3D6F454aXIFDrYYzC2PQY+CKJoXZW0m0RMWaTM2B8tBmFFwrmaGLDqcjjpd33TsTtsV5DB7WimffLBPpOuGV4Z1Kiagp/mxpPLz2NImNV79mDX9gY3ZppCZTwChP5qQ==',
				],
				Http::STATUS_CREATED,
			],
			'not updated' => [
				'bb9b52140661ee4f2c31e02ea50a8f67ba353bffc58aa981718f90bd2aa2bd8fc08cad4c0b3ed8f7eb9d79d6a577be75d084bbeb963da1ad74d9279e0014e472',
				self::$devicePublicKey,
				'https://push-notifications.nextcloud.com/',
				true,
				42,
				true,
				false,
				[
					'publicKey' => self::$userPublicKey,
					'deviceIdentifier' => 'x9vSImcGjhzR9BfZ/XbbUqqCCNC4bHKsX7vkQWNZRd1/MiY+OuF02fx8K08My0RpkNnwj/rQ/gVSU1oEdFwkww==',
					'signature' => 'J9AcdJt5youJmMnBhS+Cc9ytArynIKtCRoNf/m0oOFO/e0hWHqs1NRdQBe81qzYIjf0+bj0Q97X9Xv1rnVJesPkQUbGaa4nAPt+viGSfvzTptjX4LKgqm8B3UkduBA262IcaWgM5P84gUqelkQIC1nIqq/MJTuC6oQ5lUwIV1a92ZurDjhwH4b3f7/ZLTTOTRD0DWN9W/yOyF1qECivgePR3eu+mkcBzXVU/TDZDJic9G7xhqcTnWV6qk+aKyzdNo1tu5W7mF+v5vF6rrGZrq55vPLWAHApTD7P+NFV01BnaCuN7/qGJNVs7m7EH03jpOw7y3jqNMmcmonYrJSMVqg==',
				],
				Http::STATUS_OK,
			],
		];
	}

	/**
	 * @dataProvider dataRegisterDevice
	 */
	public function testRegisterDevice(string $pushTokenHash, string $devicePublicKey, string $proxyServer, bool $userIsValid, int $tokenId, bool $tokenIsValid, ?bool $deviceCreated, array $payload, int $status): void {
		$controller = $this->getController([
			'savePushToken',
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
			$token->expects($this->once())
				->method('getId')
				->willReturn($tokenId);
			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willReturn($token);

			$key = $this->createMock(Key::class);
			$key->expects($this->once())
				->method('getPrivate')
				->willReturn(self::$userPrivateKey);
			$key->expects($this->once())
				->method('getPublic')
				->willReturn(self::$userPublicKey);

			$this->identityProof->expects($this->once())
				->method('getKey')
				->with($user)
				->willReturn($key);

			$controller->expects($this->once())
				->method('savePushToken')
				->with($user, $token, $this->anything(), $devicePublicKey, $pushTokenHash, $proxyServer)
				->willReturn($deviceCreated);
		} else {
			$controller->expects($this->never())
				->method('savePushToken');

			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willThrowException(new InvalidTokenException());
		}

		$response = $controller->registerDevice($pushTokenHash, $devicePublicKey, $proxyServer);
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame($status, $response->getStatus());
		$this->assertSame($payload, $response->getData());
	}

	public static function dataRemoveDevice(): array {
		return [
			'not authenticated' => [
				false,
				0,
				false,
				null,
				[],
				Http::STATUS_UNAUTHORIZED
			],
			'invalid token' => [
				true,
				23,
				false,
				null,
				['message' => 'INVALID_SESSION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'using production' => [
				true,
				23,
				false,
				null,
				['message' => 'INVALID_SESSION_TOKEN'],
				Http::STATUS_BAD_REQUEST,
			],
			'created or updated' => [
				true,
				23,
				true,
				true,
				[],
				Http::STATUS_ACCEPTED,
			],
			'not updated' => [
				true,
				42,
				true,
				false,
				[],
				Http::STATUS_OK,
			],
		];
	}


	/**
	 * @dataProvider dataRemoveDevice
	 */
	public function testRemoveDevice(bool $userIsValid, int $tokenId, bool $tokenIsValid, ?bool $deviceDeleted, array $payload, int $status): void {
		$controller = $this->getController([
			'deletePushToken',
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
				->method('deletePushToken')
				->with($user, $token)
				->willReturn($deviceDeleted);
		} else {
			$controller->expects($this->never())
				->method('deletePushToken');

			$this->tokenProvider->expects($this->any())
				->method('getTokenById')
				->with($tokenId)
				->willThrowException(new InvalidTokenException());
		}

		$response = $controller->removeDevice();
		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame($status, $response->getStatus());
		$this->assertSame($payload, $response->getData());
	}
}
