<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit\WebPush;

use OCA\Notifications\Vendor\Http\Promise\Promise;
use OCA\Notifications\Vendor\Psr\Http\Message\ResponseInterface;
use OCA\Notifications\WebPush\PromiseAdapter;
use OCP\Http\Client\IPromise;
use OCP\Http\Client\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class PromiseAdapterTest extends TestCase {
	protected IPromise&MockObject $promise;

	protected function setUp(): void {
		parent::setUp();
		$this->promise = $this->createMock(IPromise::class);
	}

	protected function fulfillWith(int $statusCode, array $headers = []): void {
		$response = $this->createMock(IResponse::class);
		$response->method('getStatusCode')->willReturn($statusCode);
		$response->method('getHeaders')->willReturn($headers);
		$response->method('getBody')->willReturn('');

		$this->promise->method('then')
			->willReturnCallback(function (?callable $onFulfilled) use ($response): IPromise {
				$onFulfilled($response);
				return $this->promise;
			});
	}

	public function testFulfilledPromiseCallsTheResponseCallbackWhenAwaited(): void {
		$this->fulfillWith(429, ['Retry-After' => ['120']]);

		$adapter = new PromiseAdapter($this->promise);

		$responses = [];
		$adapter->then(static function (ResponseInterface $response) use (&$responses): void {
			$responses[] = $response;
		});

		$this->assertSame([], $responses, 'Callbacks must not run before the promise is awaited');

		$adapter->wait();

		$this->assertCount(1, $responses);
		$this->assertSame(429, $responses[0]->getStatusCode());
		$this->assertSame(['120'], $responses[0]->getHeader('Retry-After'));
	}

	public function testRejectedPromiseCallsTheFailureCallbackWithoutThrowing(): void {
		$exception = new \RuntimeException('Connection refused');
		$this->promise->method('then')->willReturn($this->promise);
		$this->promise->method('wait')->willThrowException($exception);

		$adapter = new PromiseAdapter($this->promise);

		$reasons = [];
		$adapter->then(null, static function (\Throwable $reason) use (&$reasons): void {
			$reasons[] = $reason;
		});

		$this->assertNull($adapter->wait());
		$this->assertSame([$exception], $reasons);
	}

	public function testRejectionIsThrownWhenNoFailureCallbackIsRegistered(): void {
		$exception = new \RuntimeException('Connection refused');
		$this->promise->method('then')->willReturn($this->promise);
		$this->promise->method('wait')->willThrowException($exception);

		$adapter = new PromiseAdapter($this->promise);

		$this->expectExceptionObject($exception);
		$adapter->wait();
	}

	public function testCallbacksAreOnlyCalledOnce(): void {
		$this->fulfillWith(201);

		$adapter = new PromiseAdapter($this->promise);

		$calls = 0;
		$adapter->then(static function () use (&$calls): void {
			$calls++;
		});

		$adapter->wait();
		$adapter->wait();

		$this->assertSame(1, $calls);
	}

	public function testStateIsMappedFromTheNextcloudPromise(): void {
		$this->promise->method('then')->willReturn($this->promise);
		$this->promise->method('getState')->willReturn(IPromise::STATE_PENDING);

		$adapter = new PromiseAdapter($this->promise);

		$this->assertSame(Promise::PENDING, $adapter->getState());
	}
}
