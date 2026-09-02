<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit;

use OCA\Notifications\Vendor\Minishlink\WebPush\MessageSentReport;
use OCA\Notifications\WebPushClient;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IPromise;
use OCP\Http\Client\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class WebPushClientTest extends TestCase {
	// They are testing values, do not use in production
	protected static string $uaPublicKey = 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcxaOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4';
	protected static string $auth = 'BTBZMqHH6r4Tts7J_aSIgg';

	protected IAppConfig&MockObject $appConfig;
	protected IClientService&MockObject $clientService;

	protected function setUp(): void {
		parent::setUp();
		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->clientService = $this->createMock(IClientService::class);
	}

	public function testConstructSucceedsWhenVapidKeysAreStored(): void {
		$this->appConfig->method('getAppValueString')
			->willReturnMap([
				['webpush_vapid_pubkey', '', false, 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcxaOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw'],
				['webpush_vapid_privkey', '', false, 'test-private-key'],
			]);

		$this->appConfig->expects($this->never())->method('setAppValueString');

		$client = new WebPushClient($this->appConfig, $this->clientService);
		$this->assertInstanceOf(WebPushClient::class, $client);
	}

	public function testConstructRegeneratesVapidKeysWhenDecryptionFails(): void {
		// Simulates the case where the stored VAPID keys were encrypted with a
		// different instance secret — getAppValueString throws during decryption.
		$this->appConfig->method('getAppValueString')
			->willThrowException(new \RuntimeException('HMAC does not match.'));

		$this->appConfig->expects($this->exactly(2))
			->method('setAppValueString')
			->with($this->logicalOr(
				$this->equalTo('webpush_vapid_pubkey'),
				$this->equalTo('webpush_vapid_privkey'),
			));

		// Must not throw — corrupted keys should be transparently regenerated
		$client = new WebPushClient($this->appConfig, $this->clientService);
		$this->assertInstanceOf(WebPushClient::class, $client);
	}

	public function testConstructRegeneratesVapidKeysWhenMissing(): void {
		$this->appConfig->method('getAppValueString')
			->willReturnMap([
				['webpush_vapid_pubkey', '', false, ''],
				['webpush_vapid_privkey', '', false, ''],
			]);

		$this->appConfig->expects($this->exactly(2))
			->method('setAppValueString')
			->with($this->logicalOr(
				$this->equalTo('webpush_vapid_pubkey'),
				$this->equalTo('webpush_vapid_privkey'),
			));

		$client = new WebPushClient($this->appConfig, $this->clientService);
		$this->assertInstanceOf(WebPushClient::class, $client);
	}

	public function testFlushSendsQueuedNotificationsWithTheNextcloudClient(): void {
		$this->appConfig->method('getAppValueString')->willReturn('');

		$response = $this->createMock(IResponse::class);
		$response->method('getStatusCode')->willReturn(410);
		$response->method('getHeaders')->willReturn([]);
		$response->method('getBody')->willReturn('');

		$promise = $this->createMock(IPromise::class);
		$promise->method('then')
			->willReturnCallback(static function (?callable $onFulfilled) use ($promise, $response): IPromise {
				$onFulfilled($response);
				return $promise;
			});

		$httpClient = $this->createMock(IClient::class);
		$httpClient->expects($this->once())
			->method('postAsync')
			->with(
				'https://push.example.com/endpoint',
				$this->callback(static function (array $options): bool {
					return $options['http_errors'] === false
						&& $options['headers']['Urgency'] === ['high']
						&& $options['headers']['Content-Encoding'] === ['aes128gcm']
						&& $options['body'] !== '';
				}),
			)
			->willReturn($promise);

		$this->clientService->method('newClient')->willReturn($httpClient);

		$client = new WebPushClient($this->appConfig, $this->clientService);
		$client->enqueue(
			'https://push.example.com/endpoint',
			self::$uaPublicKey,
			self::$auth,
			'{"message":"test"}',
			'high',
		);

		$reports = [];
		$client->flush(static function (MessageSentReport $report) use (&$reports): void {
			$reports[] = $report;
		});

		$this->assertCount(1, $reports);
		$this->assertFalse($reports[0]->isSuccess());
		$this->assertTrue($reports[0]->isSubscriptionExpired());
		$this->assertSame('https://push.example.com/endpoint', $reports[0]->getEndpoint());
	}
}
