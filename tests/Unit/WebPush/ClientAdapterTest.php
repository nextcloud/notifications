<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit\WebPush;

use OCA\Notifications\Vendor\GuzzleHttp\Psr7\Request;
use OCA\Notifications\Vendor\Http\Promise\Promise;
use OCA\Notifications\WebPush\ClientAdapter;
use OCA\Notifications\WebPush\ClientException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IPromise;
use OCP\Http\Client\IResponse;
use OCP\Http\Client\LocalServerException;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ClientAdapterTest extends TestCase {
	protected IClient&MockObject $client;
	protected ClientAdapter $adapter;

	protected function setUp(): void {
		parent::setUp();
		$this->client = $this->createMock(IClient::class);
		$this->adapter = new ClientAdapter($this->client);
	}

	protected static function newRequest(string $method = 'POST'): Request {
		return new Request(
			$method,
			'https://push.example.com/endpoint',
			['TTL' => '2419200', 'Urgency' => 'normal'],
			'encrypted-payload',
		);
	}

	public function testSendRequestPassesMethodUriHeadersAndBody(): void {
		$response = $this->createMock(IResponse::class);
		$response->method('getStatusCode')->willReturn(201);
		$response->method('getHeaders')->willReturn(['Location' => ['https://push.example.com/receipt']]);
		$response->method('getBody')->willReturn('');

		$this->client->expects($this->once())
			->method('request')
			->with(
				'POST',
				'https://push.example.com/endpoint',
				$this->callback(static function (array $options): bool {
					return $options['body'] === 'encrypted-payload'
						&& $options['http_errors'] === false
						&& $options['headers']['TTL'] === ['2419200'];
				}),
			)
			->willReturn($response);

		$psrResponse = $this->adapter->sendRequest(self::newRequest());

		$this->assertSame(201, $psrResponse->getStatusCode());
		$this->assertSame(['https://push.example.com/receipt'], $psrResponse->getHeader('Location'));
	}

	public function testSendRequestWrapsFailuresInAClientException(): void {
		$this->client->method('request')
			->willThrowException(new LocalServerException('Host violates local access rules'));

		$this->expectException(ClientException::class);
		$this->adapter->sendRequest(self::newRequest());
	}

	public function testSendAsyncRequestReturnsAPromiseForTheResponse(): void {
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

		$this->client->expects($this->once())
			->method('postAsync')
			->with('https://push.example.com/endpoint', $this->anything())
			->willReturn($promise);

		$reports = [];
		$this->adapter->sendAsyncRequest(self::newRequest())
			->then(static function ($psrResponse) use (&$reports): void {
				$reports[] = $psrResponse->getStatusCode();
			})
			->wait();

		$this->assertSame([410], $reports);
	}

	public function testSendAsyncRequestRejectsWhenTheRequestCanNotBeStarted(): void {
		$this->client->method('postAsync')
			->willThrowException(new LocalServerException('Host violates local access rules'));

		$promise = $this->adapter->sendAsyncRequest(self::newRequest());

		$this->assertSame(Promise::REJECTED, $promise->getState());
	}

	public function testSendAsyncRequestRejectsMethodsOtherThanPost(): void {
		$this->client->expects($this->never())
			->method('postAsync');

		$promise = $this->adapter->sendAsyncRequest(self::newRequest('GET'));

		$this->assertSame(Promise::REJECTED, $promise->getState());
	}
}
