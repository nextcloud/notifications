<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit;

use OCA\Notifications\WebPushClient;
use OCP\AppFramework\Services\IAppConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class WebPushClientTest extends TestCase {
	protected IAppConfig&MockObject $appConfig;
	protected LoggerInterface&MockObject $logger;

	protected function setUp(): void {
		parent::setUp();
		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->logger = $this->createMock(LoggerInterface::class);
	}

	public function testConstructSucceedsWhenVapidKeysAreStored(): void {
		$this->appConfig->method('getAppValueString')
			->willReturnMap([
				['webpush_vapid_pubkey', '', false, 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcxaOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw'],
				['webpush_vapid_privkey', '', false, 'test-private-key'],
			]);

		$this->appConfig->expects($this->never())->method('setAppValueString');

		$client = new WebPushClient($this->appConfig, $this->logger);
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
		$client = new WebPushClient($this->appConfig, $this->logger);
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

		$client = new WebPushClient($this->appConfig, $this->logger);
		$this->assertInstanceOf(WebPushClient::class, $client);
	}

	public function testFlushAsyncWithoutQueuedNotifications(): void {
		$client = new WebPushClient($this->appConfig, $this->logger);

		$this->assertSame([], $client->flushAsync(static function (): void {
		}));
	}

	public function testFlushAsyncSkipsNotificationsThatCanNotBePrepared(): void {
		$client = new WebPushClient($this->appConfig, $this->logger);

		// Not a valid public key, so the encryption fails while the pool
		// prepares the request
		$client->enqueue('https://example.tld/endpoint', str_repeat('A', 87), str_repeat('B', 22), '{"nid":1}');

		// The library logs through the adapter, which only implements log()
		$this->logger->expects($this->once())
			->method('log')
			->with('error', $this->stringContains('Failed to prepare push notification'), $this->anything());

		// One batch, but the request of the broken subscription is skipped,
		// so nothing is sent and waiting for the promise is not needed
		$promises = $client->flushAsync(static function (): void {
		});
		$this->assertCount(1, $promises);
	}
}
