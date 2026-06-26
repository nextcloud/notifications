<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\Authentication\Token\PublicKeyToken;
use OC\Security\IdentityProof\Key;
use OC\Security\IdentityProof\Manager;
use OCA\Notifications\Exceptions\InvalidDeviceTokenException;
use OCA\Notifications\Push;
use OCA\Notifications\WebPushClient;
use OCP\AppFramework\Http;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Authentication\Token\IToken as OCPIToken;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\IAppConfig as IGlobalAppConfig;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IUser;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\IManager as INotificationManager;
use OCP\Notification\INotification;
use OCP\Security\ISecureRandom;
use OCP\UserStatus\IManager as IUserStatusManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Test\TestCase;

#[Group(name: 'DB')]
class PushTest extends TestCase {
	protected IDBConnection $db;
	protected INotificationManager&MockObject $notificationManager;
	protected IConfig&MockObject $config;
	protected IAppConfig&MockObject $appConfig;
	protected IGlobalAppConfig&MockObject $globalAppConfig;
	protected WebPushClient&MockObject $wpClient;
	protected IProvider&MockObject $tokenProvider;
	protected Manager&MockObject $keyManager;
	protected IClientService&MockObject $clientService;
	protected ICacheFactory&MockObject $cacheFactory;
	protected ICache&MockObject $cache;
	protected IUserStatusManager&MockObject $userStatusManager;
	protected IFactory&MockObject $l10nFactory;
	protected ITimeFactory&MockObject $timeFactory;
	protected ISecureRandom&MockObject $random;
	protected LoggerInterface&MockObject $logger;
	protected IUserManager&MockObject $userManager;

	public const EX_UA_PUBLIC = 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4';
	public const EX_AUTH = 'BTBZMqHH6r4Tts7J_aSIgg';

	protected function setUp(): void {
		parent::setUp();

		$this->db = \OCP\Server::get(IDBConnection::class);
		$this->notificationManager = $this->createMock(INotificationManager::class);
		$this->config = $this->createMock(IConfig::class);
		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->globalAppConfig = $this->createMock(IGlobalAppConfig::class);
		$this->wpClient = $this->createMock(WebPushClient::class);
		$this->tokenProvider = $this->createMock(IProvider::class);
		$this->keyManager = $this->createMock(Manager::class);
		$this->clientService = $this->createMock(IClientService::class);
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->cache = $this->createMock(ICache::class);
		$this->userStatusManager = $this->createMock(IUserStatusManager::class);
		$this->l10nFactory = $this->createMock(IFactory::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->random = $this->createMock(ISecureRandom::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->userManager = $this->createMock(IUserManager::class);

		$this->cacheFactory->method('createDistributed')
			->with('pushtokens')
			->willReturn($this->cache);

		$this->appConfig->method('getAppValueBool')
			->with('webpush_enabled')
			->willReturn(true);
	}

	/**
	 * @param string[] $methods
	 */
	protected function getPush(array $methods = []): Push|MockObject {
		if (!empty($methods)) {
			return $this->getMockBuilder(Push::class)
				->setConstructorArgs([
					$this->db,
					$this->userManager,
					$this->notificationManager,
					$this->config,
					$this->appConfig,
					$this->globalAppConfig,
					$this->wpClient,
					$this->tokenProvider,
					$this->keyManager,
					$this->clientService,
					$this->cacheFactory,
					$this->userStatusManager,
					$this->l10nFactory,
					$this->timeFactory,
					$this->random,
					$this->logger,
				])
				->onlyMethods($methods)
				->getMock();
		}

		return new Push(
			$this->db,
			$this->userManager,
			$this->notificationManager,
			$this->config,
			$this->appConfig,
			$this->globalAppConfig,
			$this->wpClient,
			$this->tokenProvider,
			$this->keyManager,
			$this->clientService,
			$this->cacheFactory,
			$this->userStatusManager,
			$this->l10nFactory,
			$this->timeFactory,
			$this->random,
			$this->logger,
		);
	}

	public function testPushToDeviceNoInternet(): void {
		$push = $this->getPush();

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(false);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');
		$this->userManager->expects($this->never())
			->method('getExistingUser');

		/** @var INotification&MockObject$notification */
		$notification = $this->createStub(INotification::class);

		$push->pushToDevice(23, $notification);
	}

	public function testPushToDeviceNoDevices(): void {
		$push = $this->getPush(['getProxyDevicesForUser']);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getProxyDevicesForUser')
			->willReturn([]);

		$push->pushToDevice(42, $notification);
	}

	public function testPushToDeviceNotPrepared(): void {
		$push = $this->getPush(['getProxyDevicesForUser']);
		$this->keyManager->expects($this->never())
			->method('getKey');
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getProxyDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 'token1',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('de');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'de')
			->willThrowException(new \InvalidArgumentException());

		$push->pushToDevice(1337, $notification);
	}

	public function testProxyPushToDeviceInvalidToken(): void {
		$push = $this->getPush(['getProxyDevicesForUser', 'encryptAndSign', 'deleteProxyPushToken']);
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getProxyDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 23,
				'apptype' => 'other',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key&MockObject $key */
		$key = $this->createStub(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$this->tokenProvider->expects($this->once())
			->method('getTokenById')
			->willThrowException(new InvalidTokenException());

		$push->expects($this->never())
			->method('encryptAndSign');

		$push->expects($this->once())
			->method('deleteProxyPushToken')
			->with(23);

		$push->pushToDevice(2018, $notification);
	}

	public function testProxyPushToDeviceEncryptionError(): void {
		$push = $this->getPush(['getProxyDevicesForUser', 'encryptAndSign', 'deleteProxyPushToken', 'filterByTokenAge']);
		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getProxyDevicesForUser')
			->willReturn([[
				'proxyserver' => 'proxyserver1',
				'token' => 23,
				'apptype' => 'other',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key&MockObject $key */
		$key = $this->createStub(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->once())
			->method('filterByTokenAge')
			->willReturnArgument(0);

		$push->expects($this->once())
			->method('encryptAndSign')
			->willThrowException(new \InvalidArgumentException());

		$push->expects($this->once())
			->method('deleteProxyPushToken')
			->with(23);

		$push->pushToDevice(1970, $notification);
	}

	public function testProxyPushToDeviceNoFairUse(): void {
		$push = $this->getPush(['getProxyDevicesForUser', 'encryptAndSign', 'deleteProxyPushToken', 'filterByTokenAge', 'deleteProxyPushTokenByDeviceIdentifier']);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getProxyDevicesForUser')
			->willReturn([
				[
					'proxyserver' => 'proxyserver',
					'token' => 16,
					'apptype' => 'other',
				],
			]);

		$this->config
			->method('getSystemValue')
			->with('debug', false)
			->willReturn(false);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key&MockObject $key */
		$key = $this->createStub(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->once())
			->method('filterByTokenAge')
			->willReturnArgument(0);

		$push->expects($this->exactly(1))
			->method('encryptAndSign')
			->willReturn(['Payload']);

		$push->expects($this->never())
			->method('deleteProxyPushToken');

		$this->clientService->expects($this->never())
			->method('newClient');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		$this->notificationManager->method('isFairUseOfFreePushService')
			->willReturn(false);

		$push->method('deleteProxyPushTokenByDeviceIdentifier')
			->with('proxyserver', '123456');

		$push->pushToDevice(207787, $notification);
	}

	public static function dataProxyPushToDeviceSending(): array {
		return [
			[true, 'PKCS1'],
			[true, 'OAEP'],
			[false, 'PKCS1'],
			[false, 'OAEP'],
		];
	}

	#[DataProvider(methodName: 'dataProxyPushToDeviceSending')]
	public function testPushToDeviceSending(bool $isDebug, string $padding): void {
		$push = $this->getPush(['getProxyDevicesForUser', 'deleteProxyPushToken', 'filterByTokenAge', 'deleteProxyPushTokenByDeviceIdentifier']);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		$notification
			->method('getParsedSubject')
			->willReturn('ParsedSubject');

		$notification
			->method('getApp')
			->willReturn('PushTestApp');

		$notification
			->method('getObjectType')
			->willReturn('PushTestType');

		$notification
			->method('getObjectId')
			->willReturn('PushTestObjectId');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$devicePublicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1SN9sDJGNifnEv/y1UkP
tggJA0xks5b0WN/Ida3GYK4Zy/ZWTa3wAknCerKkZC2rrFbcP55HA/oSp8fuUJC3
q4b59znuhGoQtvvdAwUx6qSIPheAGs/gfMpNWO/bfH02oBu+98eTkxciuNKPxBFk
wRdSSUxsHwkzCOw+er6oxriVSkc7tsNVaXg+ZpzW15cUQugjT6JDDjg5ftSeGsLj
VV70QXge4uD3ege/lsa1N8iUVCjeMJHobyQm/hhGE990b6BzTgOIC1pGsOPbOsZB
/5n54G4EUX9dixSSF90fDJs83GWQ+AIjf/uHmj3vFMe1bnqIwq9P17+IWe5x9Z04
FQIDAQAB
-----END PUBLIC KEY-----';

		$push->expects($this->once())
			->method('getProxyDevicesForUser')
			->willReturn([
				[
					'proxyserver' => 'proxyserver1',
					'token' => 16,
					'apptype' => 'other',
					'devicepublickey' => $devicePublicKey,
					'deviceidentifier' => 'ident16',
					'pushtokenhash' => 'hash16',
				],
				[
					'proxyserver' => 'proxyserver1/',
					'token' => 23,
					'apptype' => 'other',
					'devicepublickey' => $devicePublicKey,
					'deviceidentifier' => 'ident23',
					'pushtokenhash' => 'hash23',
				],
				[
					'proxyserver' => 'badrequest',
					'token' => 42,
					'apptype' => 'other',
					'devicepublickey' => $devicePublicKey,
					'deviceidentifier' => 'ident42',
					'pushtokenhash' => 'hash42',
				],
				[
					'proxyserver' => 'unavailable',
					'token' => 48,
					'apptype' => 'other',
					'devicepublickey' => $devicePublicKey,
					'deviceidentifier' => 'ident48',
					'pushtokenhash' => 'hash48',
				],
				[
					'proxyserver' => 'ok',
					'token' => 64,
					'apptype' => 'other',
					'devicepublickey' => $devicePublicKey,
					'deviceidentifier' => 'ident64',
					'pushtokenhash' => 'hash64',
				],
				[
					'proxyserver' => 'badrequest-with-devices',
					'token' => 128,
					'apptype' => 'other',
					'devicepublickey' => $devicePublicKey,
					'deviceidentifier' => 'ident128',
					'pushtokenhash' => 'hash128',
				],
			]);

		$this->config
			->method('getSystemValue')
			->with('debug', false)
			->willReturn($isDebug);

		$this->appConfig
			->expects($this->exactly(7))
			->method('getAppValueString')
			->willReturnMap([
				['subscription_aware_server', 'https://push-notifications.nextcloud.com', 'https://push-notifications.nextcloud.com'],
				['push_encryption_padding', 'OAEP', $padding],
			]);

		$this->globalAppConfig
			->method('getValueString')
			->with('support', 'subscription_key')
			->willReturn('');

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key&MockObject $key */
		$key = $this->createMock(Key::class);

		$key->method('getPrivate')
			->willReturn('-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCkGi7rj6BWi9U8
lptb1rBKVFENFJjq690Ap8wAdR9cxGYE+nzdFxlcRxh17GwyWaBDEPWtL5WDPxi0
8XjQWpwfYFzjMLLzGMZaj7Jiam5rJM12qFEvYyw9s26LzebeLTzvp28L2TIWCswi
dM0OvrYGGvL9f+N+6Oev5cwiSJk0WrHceL+P/wFH3mmOdOBHmKkofeDZoS1XkNOE
o1EpwepvDibUjc3vHPk1IHg4hsfKk1O9+uHiBf9xqm//M9MKrM9mlOButbo2bOhu
1YN/2wKww7bU/c7JYgVeljpfN0sMuKmhMiuje2KqOU8v8yIREkKMyxd4+B+s3eNG
PdVBf57FAgMBAAECggEAAMegdOm3xPyXMk+f0yNlh5kzDKE1itCL4aYIA6LZnLmp
bHHJWZI4kTT1IHPdgDeYlPBjgAOuEFEsjhLySoXwoF4BIIgErCKD9xa9pBIojmVm
LovlNfFlvScDKgsjUed+5ZguDLcYO7gyQvqTzS6ivXzq6TezeVDfeUgBTg3JG6n/
BjgIek1jV4q9OrQTOdR3eAPwW6pbLG820OB87Cx/A0JHdEgCevFCHmMUbk7yXQrh
W/JwTrnV66LBPjSWQcsHUNWrl2nqV08BYoC1uy32uTpUlejCdWDuHuB1DYOpn0KY
nyiVTNF/MZ0E9sOsKKgbodBz4n3cc6v7NckpFpKMHwKBgQDUeaakKYwatNg6YEHP
OB3iH62bAe7Ej6vg0EPYgr6fzRjAETzTrpZZfzmrGwTxFBczFrx8hOPsyDkpiEU5
77L90jMOb9NSasjS8Pv0GM2LY/8H8Ck4RwXF/E3maSbc2bAY9ZXH6m82YW6iHnfc
G9L0xVCq549axd1tWUnXdukiuwKBgQDFt9KDtlwMutuQOj+Eup7L0sMAxO4jlghH
hTxSAqQ5mlodbQhULXRk4QWGMjjKG0y4XY2rw+VgBQzFT8W3jlThR3BMWKNv3P+M
zlzkoxcoOio7K25im4Yb0NheOnaxaqhLcRwXcxa5E9kn+108xYIYAACZgmkZo7ab
PCoQicnsfwKBgAzyEIYmBeRGqnn8DWZru95gIbq1BnAxdL5w0gFqDeU8oMprAnK/
S2fOiZv0PHvXxoYVV4yaqCxwEpOGOvmJsjUmzneNtqlp2iyIBEHeFP/uKsa4Cjrk
kOR8N97W/0grd0A+Dk8s6HO+wffctV7SzyqcrwqKq0BTl+cmrooTM6crAoGATjjT
iFh1QnQKuZzR1GkgufLAQ2Wl8V5CGEmV+7wfzMpMLKgeS29QRTjhPp5P6WWzjJ02
l2YBMWPOEaHlzyD4Y8gnnYzT3EXKtKJQDgSX/MpGOvKL0WdGP2r4rw7iNn7D5lTx
kDVwH/jCSRchZBGfzm7xzcnSWtpyPCgpXDGnOXECgYEAyqDrBeKlUDDltoe6xs4K
gEG+V5E5cwZqvSoOlYHqbtP9Dms6z6G8TvPZblwlbgwXFofrd5LN/vK3pZPiU5Y+
sd7MhWnjKf7EX9GJD0VhLabFY/KrloJkyL7gOY21xFvmnNqwvH60eOxbVPzlYjaN
96rK6qkqEdUgXj0CpJZHAMw=
-----END PRIVATE KEY-----');

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		$push->expects($this->once())
			->method('filterByTokenAge')
			->willReturnArgument(0);

		$push->expects($this->never())
			->method('deleteProxyPushToken');

		/** @var IClient&MockObject $client */
		$client = $this->createMock(IClient::class);

		$this->clientService->expects($this->once())
			->method('newClient')
			->willReturn($client);

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		// Call 1
		/** @var ResponseInterface&MockObject $response1 */
		$response1 = $this->createMock(ResponseInterface::class);
		$response1->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_BAD_REQUEST);
		/** @var StreamInterface&MockObject $body1 */
		$body1 = $this->createMock(StreamInterface::class);
		$body1->expects($this->once())
			->method('getContents')
			->willReturn('');
		$response1->expects($this->once())
			->method('getBody')
			->willReturn($body1);
		$exception1 = $this->createMock(ClientException::class);
		$exception1->method('getResponse')
			->willReturn($response1);

		// Call 2
		/** @var ResponseInterface&MockObject $response1 */
		$response2 = $this->createMock(ResponseInterface::class);
		/** @var StreamInterface&MockObject $body2 */
		$body2 = $this->createMock(StreamInterface::class);
		$body2->expects($this->once())
			->method('getContents')
			->willReturn('Maintenance');
		$response2->expects($this->once())
			->method('getBody')
			->willReturn($body2);
		$exception2 = $this->createMock(ServerException::class);
		$exception2->method('getResponse')
			->willReturn($response2);

		// Call 3
		/** @var IResponse&MockObject $response1 */
		$response3 = $this->createMock(IResponse::class);
		$response3->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_OK);
		/** @var StreamInterface&MockObject $body3 */
		$body3 = $this->createStub(StreamInterface::class);
		$response3->method('getBody')
			->willReturn('');

		// Call 4
		/** @var ResponseInterface&MockObject $response1 */
		$response4 = $this->createMock(ResponseInterface::class);
		$response4->expects($this->once())
			->method('getStatusCode')
			->willReturn(Http::STATUS_BAD_REQUEST);
		/** @var StreamInterface&MockObject $body4 */
		$body4 = $this->createMock(StreamInterface::class);
		$body4->expects($this->once())
			->method('getContents')
			->willReturn(json_encode([
				'failed' => 1,
				'unknown' => [
					'123456'
				]
			]));
		$response4->expects($this->once())
			->method('getBody')
			->willReturn($body4);
		$exception4 = $this->createMock(ClientException::class);
		$exception4->method('getResponse')
			->willReturn($response4);

		$exception0 = new \Exception();
		$client->expects($this->exactly(5))
			->method('post')
			->willReturnOnConsecutiveCalls(
				$this->throwException($exception0), // proxyserver1/notifications
				$this->throwException($exception1), // badrequest/notifications
				$this->throwException($exception2), // unavailable/notifications
				$response3, // ok/notifications
				$this->throwException($exception4),
			);

		$this->logger->expects($this->atLeastOnce())
			->method('error')
			->with($exception0->getMessage(), [
				'exception' => $exception0,
			]);
		$this->logger->expects($this->once())
			->method('warning')
			->with('Could not send notification to push server [{url}]: {error}', [
				'error' => 'no reason given',
				'url' => 'badrequest',
				'app' => 'notifications',
			]);
		$this->logger->expects($this->once())
			->method('debug')
			->with('Could not send notification to push server [{url}]: {error}', [
				'error' => 'Maintenance',
				'url' => 'unavailable',
				'app' => 'notifications',
			]);

		$this->notificationManager->method('isFairUseOfFreePushService')
			->willReturn(true);

		$push->method('deleteProxyPushTokenByDeviceIdentifier')
			->with('badrequest-with-devices', '123456');

		/** @var OutputInterface&MockObject $user */
		$output = $this->createMock(OutputInterface::class);
		$foundExpectedJsonData = false;
		$output
			->method('writeln')
			->willReturnCallback(function ($string) use (&$foundExpectedJsonData) {
				if (str_contains($string, '"subject":"ParsedSubject"')
					&& str_contains($string, '"nid":207787')
					&& str_contains($string, '"type":"PushTestType"')
					&& str_contains($string, '"id":"PushTestObjectId"')
					&& str_contains($string, '"app":"PushTestApp"')) {

					$foundExpectedJsonData = true;
				}
			});

		$push->setOutput($output);

		$push->pushToDevice(207787, $notification);

		$this->assertTrue($foundExpectedJsonData, 'Failed to find correct subject, nid, type, id or app in json encoded push data');
	}

	public static function dataProxyPushToDeviceTalkNotification(): array {
		return [
			[['nextcloud'], false, 0],
			[['nextcloud'], true, 0],
			[['nextcloud', 'talk'], false, 0],
			[['nextcloud', 'talk'], true, 1],
			[['talk', 'nextcloud'], false, 1],
			[['talk', 'nextcloud'], true, 0],
			[['talk'], false, null],
			[['talk'], true, 0],
		];
	}

	/**
	 * @param string[] $deviceTypes
	 */
	#[DataProvider(methodName: 'dataProxyPushToDeviceTalkNotification')]
	public function testProxyPushToDeviceTalkNotification(array $deviceTypes, bool $isTalkNotification, ?int $pushedDevice): void {
		$push = $this->getPush(['getProxyDevicesForUser', 'encryptAndSign', 'deleteProxyPushToken', 'filterByTokenAge']);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');

		if ($isTalkNotification) {
			$notification->expects($this->any())
				->method('getApp')
				->willReturn('spreed');
		} else {
			$notification->expects($this->any())
				->method('getApp')
				->willReturn('notifications');
		}

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$devices = [];
		foreach ($deviceTypes as $deviceType) {
			$devices[] = [
				'proxyserver' => 'proxyserver',
				'token' => strlen($deviceType),
				'apptype' => $deviceType,
			];
		}
		$push->expects($this->once())
			->method('getProxyDevicesForUser')
			->willReturn($devices);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		/** @var Key&MockObject $key */
		$key = $this->createStub(Key::class);

		$this->keyManager->expects($this->once())
			->method('getKey')
			->with($user)
			->willReturn($key);

		if ($pushedDevice === null) {
			$push->expects($this->never())
				->method('filterByTokenAge');

			$push->expects($this->never())
				->method('encryptAndSign');

			$this->clientService->expects($this->never())
				->method('newClient');
		} else {
			$push->expects($this->once())
				->method('filterByTokenAge')
				->willReturnArgument(0);

			$push->expects($this->exactly(1))
				->method('encryptAndSign')
				->with($this->anything(), $devices[$pushedDevice], $this->anything(), $this->anything())
				->willReturn(['Payload']);

			/** @var IClient&MockObject $client */
			$client = $this->createMock(IClient::class);

			$this->clientService->expects($this->once())
				->method('newClient')
				->willReturn($client);

			/** @var IResponse&MockObject $response1 */
			$response = $this->createMock(IResponse::class);
			$response->expects($this->once())
				->method('getStatusCode')
				->willReturn(Http::STATUS_BAD_REQUEST);
			$response->expects($this->once())
				->method('getBody')
				->willReturn('');
			$client->expects($this->once())
				->method('post')
				->with('proxyserver/notifications', [
					'body' => [
						'notifications' => ['["Payload"]'],
					],
				])
				->willReturn($response);
		}

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		$this->appConfig
			->method('getAppValueString')
			->with('subscription_aware_server', 'https://push-notifications.nextcloud.com')
			->willReturn('https://push-notifications.nextcloud.com');

		$this->globalAppConfig
			->method('getValueString')
			->with('support', 'subscription_key')
			->willReturn('');

		$this->notificationManager->method('isFairUseOfFreePushService')
			->willReturn(true);

		$push->pushToDevice(200718, $notification);
	}

	public function testWebPushToDeviceNoDevices(): void {
		$push = $this->getPush(['getWebPushDevicesForUser']);

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');
		$notification
			->expects($this->any())
			->method('getApp')
			->willReturn('someApp');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getWebPushDevicesForUser')
			->willReturn([]);

		$push->pushToDevice(42, $notification);
	}

	public function testWebPushToDeviceNotPrepared(): void {
		$push = $this->getPush(['getWebPushDevicesForUser']);

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');
		$notification
			->expects($this->any())
			->method('getApp')
			->willReturn('someApp');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getWebPushDevicesForUser')
			->willReturn([[
				'activated' => true,
				'endpoint' => 'endpoint1',
				'ua_public' => self::EX_UA_PUBLIC,
				'auth' => self::EX_AUTH,
				'token' => 'token1',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('de');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'de')
			->willThrowException(new \InvalidArgumentException());

		$push->pushToDevice(1337, $notification);
	}

	public function testWebPushToDeviceInvalidToken(): void {
		$push = $this->getPush(['getWebPushDevicesForUser', 'encodeNotif', 'deleteWebPushToken']);

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');
		$notification
			->expects($this->any())
			->method('getApp')
			->willReturn('someApp');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getWebPushDevicesForUser')
			->willReturn([[
				'activated' => true,
				'endpoint' => 'endpoint1',
				'ua_public' => self::EX_UA_PUBLIC,
				'auth' => self::EX_AUTH,
				'token' => 23,
				'app_types' => 'all',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		$this->tokenProvider->expects($this->once())
			->method('getTokenById')
			->willThrowException(new InvalidTokenException());

		$push->expects($this->never())
			->method('encodeNotif');

		$push->expects($this->once())
			->method('deleteWebPushToken')
			->with(23);

		$push->pushToDevice(2018, $notification);
	}

	public function testWebPushToDeviceEncryptionError(): void {
		$push = $this->getPush(['getWebPushDevicesForUser', 'deleteWebPushToken', 'filterByTokenAge']);

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');
		$notification
			->expects($this->any())
			->method('getApp')
			->willReturn('someApp');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getWebPushDevicesForUser')
			->willReturn([[
				'activated' => true,
				'endpoint' => 'endpoint1',
				'ua_public' => self::EX_UA_PUBLIC,
				'auth' => self::EX_AUTH,
				'token' => 23,
				'app_types' => 'all',
			]]);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		$push->expects($this->once())
			->method('filterByTokenAge')
			->willReturnArgument(0);

		$this->wpClient->method('enqueue')
			->willThrowException(new \InvalidArgumentException());

		$push->expects($this->once())
			->method('deleteWebPushToken')
			->with(23);

		$push->pushToDevice(1970, $notification);
	}

	public static function dataWebPushToDeviceSending(): array {
		return [
			[true],
			[false],
		];
	}

	#[DataProvider(methodName: 'dataWebPushToDeviceSending')]
	public function testWebPushToDeviceSending(bool $isRateLimited): void {
		$push = $this->getPush(['getWebPushDevicesForUser', 'encodeNotif', 'deleteWebPushToken', 'filterByTokenAge']);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');
		$notification
			->expects($this->any())
			->method('getApp')
			->willReturn('someApp');

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$push->expects($this->once())
			->method('getWebPushDevicesForUser')
			->willReturn([
				[
					'activated' => true,
					'endpoint' => 'endpoint1',
					'ua_public' => self::EX_UA_PUBLIC,
					'auth' => self::EX_AUTH,
					'token' => 16,
					'app_types' => 'all',
				],
				[
					'activated' => true,
					'endpoint' => 'endpoint2',
					'ua_public' => self::EX_UA_PUBLIC,
					'auth' => self::EX_AUTH,
					'token' => 23,
					'app_types' => 'all',
				]
			]);

		$this->l10nFactory
			->expects($this->once())
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		$push->expects($this->once())
			->method('filterByTokenAge')
			->willReturnArgument(0);

		$push->expects($this->exactly($isRateLimited ? 1 : 2))
			->method('encodeNotif')
			->willReturn([
				'nid' => 1,
				'app' => 'someApp',
				'subject' => 'test',
				'type' => 'someType',
				'id' => 'someId'
			]);

		$push->expects($this->never())
			->method('deleteWebPushToken');

		$this->wpClient->expects($this->exactly($isRateLimited ? 1 : 2))
			->method('enqueue');

		if ($isRateLimited) {
			$this->cache
				->expects($this->exactly(2))
				->method('get')
				->willReturn(true, false);
		}

		$this->wpClient->expects($this->once())
			->method('flush');

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		$push->pushToDevice(207787, $notification);
	}

	public static function dataFilterWebPushDeviceList(): array {
		return [
			[false, 'all', 'myApp', false],
			[true, 'all', 'myApp', true],
			[true, 'all,-myApp', 'myApp', false],
			[true, '-myApp,all', 'myApp', false],
			[true, 'all,-other', 'myApp', true],
			[true, 'all,-talk', 'spreed', false],
			[true, 'all,-talk', 'talk', false],
			[true, 'talk', 'spreed', true],
			[true, 'talk', 'admin_notification_talk', true],
		];
	}

	/**
	 * @param string[] $deviceTypes
	 */
	#[DataProvider(methodName: 'dataFilterWebPushDeviceList')]
	public function testFilterWebPushDeviceList(bool $activated, string $deviceApptypes, string $app, bool $pass): void {
		$push = $this->getPush(['filterByTokenAge']);
		$push->method('filterByTokenAge')
			->willReturnArgument(0);

		$devices = [[
			'activated' => $activated,
			'app_types' => $deviceApptypes,
		]];
		if ($pass) {
			$result = $devices;
		} else {
			$result = [];
		}
		$this->assertEquals($result, $push->filterWebPushDeviceList($devices, $app));
	}
	/**
	 * @return array
	 * @psalm-return list<array<array, string, ?int>>
	 * list<deviceTypes, notificationApp, pushedDevice
	 */
	public static function dataWebPushToDeviceFilterApp(): array {
		return [
			[['all'], 'notifications', 0],
			[['all'], 'spreed', 0],
			[['notifications'], 'notifications', 0],
			[['notifications'], 'talk', null],
			[['notifications'], 'spreed', null],
			[['talk'], 'notifications', null],
			[['talk'], 'talk', 0],
			[['talk'], 'spreed', 0],
			[['all,-talk'], 'notifications', 0],
			[['all,-talk'], 'talk', null],
			[['all,-talk'], 'spreed', null],
			[['all,-notifications'], 'notifications', null],
			[['all,-notifications'], 'talk', 0],
			[['all,-notifications'], 'spreed', 0],
			[['all,-talk', 'talk'], 'notifications', 0],
			[['all,-talk', 'talk'], 'spreed', 1],
			[['talk', 'all'], 'notifications', 1],
			[['talk', 'all,-talk'], 'notifications', 1],
			[['talk', 'all,-talk'], 'spreed', 0],
			[['talk'], 'notifications', null],
			[['talk'], 'spreed', 0],
		];
	}

	/**
	 * @param string[] $deviceTypes
	 */
	#[DataProvider(methodName: 'dataWebPushToDeviceFilterApp')]
	public function testWebPushToDeviceFilterApp(array $deviceTypes, string $notificationApp, ?int $pushedDevice): void {
		$push = $this->getPush(['getWebPushDevicesForUser', 'encodeNotif', 'deleteWebPushToken', 'filterByTokenAge']);

		/** @var INotification&MockObject $notification */
		$notification = $this->createMock(INotification::class);
		$notification
			->method('getUser')
			->willReturn('valid');
		$notification
			->method('getApp')
			->willReturn($notificationApp);

		/** @var IUser&MockObject $user */
		$user = $this->createStub(IUser::class);

		$this->userManager->expects($this->once())
			->method('getExistingUser')
			->with('valid')
			->willReturn($user);

		$devices = [];
		foreach ($deviceTypes as $deviceType) {
			$devices[] = [
				'activated' => true,
				'endpoint' => 'endpoint',
				'ua_public' => self::EX_UA_PUBLIC,
				'auth' => self::EX_AUTH,
				'token' => strlen($deviceType),
				'app_types' => $deviceType,
			];
		}
		$push->expects($this->once())
			->method('getWebPushDevicesForUser')
			->willReturn($devices);

		$this->l10nFactory
			->method('getUserLanguage')
			->with($user)
			->willReturn('ru');

		$this->notificationManager->expects($this->once())
			->method('prepare')
			->with($notification, 'ru')
			->willReturnArgument(0);

		$push->method('filterByTokenAge')
			->willReturnArgument(0);

		if ($pushedDevice === null) {
			$push->expects($this->never())
				->method('encodeNotif');
		} else {
			$push->expects($this->exactly(1))
				->method('encodeNotif')
				->willReturn([
					'nid' => 1,
					'app' => $notificationApp,
					'subject' => 'test',
					'type' => 'someType',
					'id' => 'someId'
				]);

			$this->wpClient->expects($this->once())
				->method('enqueue')
				->with(
					'endpoint',
					self::EX_UA_PUBLIC,
					self::EX_AUTH,
					$this->anything(),
					$this->anything()
				);
		}

		$this->config->expects($this->once())
			->method('getSystemValueBool')
			->with('has_internet_connection', true)
			->willReturn(true);

		$push->pushToDevice(200718, $notification);
	}

	public static function dataValidateTokenAndGetAge(): array {
		return [
			'lastCheck newer' => [1239999999, 1230000000, 1239999999],
			'lastActivity newer' => [1230000000, 1239999999, 1239999999],
			'both equal' => [1230000000, 1230000000, 1230000000],
		];
	}

	#[DataProvider(methodName: 'dataValidateTokenAndGetAge')]
	public function testValidateTokenAndGetAge(int $lastCheck, int $lastActivity, int $expected): void {
		$token = PublicKeyToken::fromParams([
			'lastCheck' => $lastCheck,
			'lastActivity' => $lastActivity,
			'type' => OCPIToken::PERMANENT_TOKEN,
		]);

		$this->cache->expects($this->once())
			->method('get')
			->with('t42')
			->willReturn(null);
		$this->cache->expects($this->once())
			->method('set')
			->with('t42', $expected, 600);

		$this->tokenProvider->expects($this->once())
			->method('getTokenById')
			->with(42)
			->willReturn($token);

		$push = $this->getPush();
		$this->assertSame($expected, self::invokePrivate($push, 'validateTokenAndGetAge', [42]));
	}

	public function testValidateTokenAndGetAgeWipeToken(): void {
		$token = PublicKeyToken::fromParams([
			'lastCheck' => 1234567890,
			'lastActivity' => 1234567890,
			'type' => OCPIToken::WIPE_TOKEN,
		]);

		$this->cache->expects($this->once())
			->method('get')
			->with('t42')
			->willReturn(null);
		$this->cache->expects($this->once())
			->method('set')
			->with('t42', 0, 600);

		$this->tokenProvider->expects($this->once())
			->method('getTokenById')
			->with(42)
			->willReturn($token);

		$push = $this->getPush();
		$this->expectException(InvalidDeviceTokenException::class);
		self::invokePrivate($push, 'validateTokenAndGetAge', [42]);
	}

	public function testValidateTokenAndGetAgeInvalidToken(): void {
		$this->cache->expects($this->once())
			->method('get')
			->with('t42')
			->willReturn(null);
		$this->cache->expects($this->once())
			->method('set')
			->with('t42', 0, 600);

		$this->tokenProvider->expects($this->once())
			->method('getTokenById')
			->with(42)
			->willThrowException(new InvalidTokenException());

		$push = $this->getPush();
		$this->expectException(InvalidDeviceTokenException::class);
		self::invokePrivate($push, 'validateTokenAndGetAge', [42]);
	}

	public function testValidateTokenAndGetAgeSessionToken(): void {
		$this->cache->expects($this->never())
			->method('get');
		$this->tokenProvider->expects($this->never())
			->method('getTokenById');

		$push = $this->getPush();
		$this->assertSame(0, self::invokePrivate($push, 'validateTokenAndGetAge', [-1]));
	}

	public function testValidateTokenAndGetAgeCached(): void {
		$this->cache->expects($this->once())
			->method('get')
			->with('t42')
			->willReturn(1234567890);
		$this->cache->expects($this->never())
			->method('set');

		$this->tokenProvider->expects($this->never())
			->method('getTokenById');

		$push = $this->getPush();
		$this->assertSame(1234567890, self::invokePrivate($push, 'validateTokenAndGetAge', [42]));
	}
}
