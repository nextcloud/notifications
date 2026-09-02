<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\WebPush;

use OCA\Notifications\Vendor\GuzzleHttp\Psr7\Response;
use OCA\Notifications\Vendor\Http\Client\HttpAsyncClient;
use OCA\Notifications\Vendor\Http\Promise\Promise;
use OCA\Notifications\Vendor\Http\Promise\RejectedPromise;
use OCA\Notifications\Vendor\Psr\Http\Client\ClientInterface;
use OCA\Notifications\Vendor\Psr\Http\Message\RequestInterface;
use OCA\Notifications\Vendor\Psr\Http\Message\ResponseInterface;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IResponse;

/**
 * Adapts the Nextcloud HTTP client to the PSR-18 and HTTPlug client interfaces
 * of the vendored web-push library, so requests to the push services honour the
 * proxy, timeout and certificate configuration of the instance.
 */
class ClientAdapter implements ClientInterface, HttpAsyncClient {
	public function __construct(
		protected IClient $client,
	) {
	}

	#[\Override]
	public function sendRequest(RequestInterface $request): ResponseInterface {
		try {
			$response = $this->client->request(
				$request->getMethod(),
				(string)$request->getUri(),
				self::buildOptions($request),
			);
		} catch (\Throwable $e) {
			throw new ClientException($e->getMessage(), (int)$e->getCode(), $e);
		}

		return self::convertResponse($response);
	}

	/**
	 * Web push notifications are always sent as POST requests, so only those can
	 * be handed to the asynchronous API of the Nextcloud client.
	 */
	#[\Override]
	public function sendAsyncRequest(RequestInterface $request): Promise {
		if (strtoupper($request->getMethod()) !== 'POST') {
			return new RejectedPromise(new ClientException('Only POST requests can be sent asynchronously'));
		}

		try {
			$promise = $this->client->postAsync((string)$request->getUri(), self::buildOptions($request));
		} catch (\Throwable $e) {
			return new RejectedPromise($e);
		}

		return new PromiseAdapter($promise);
	}

	public static function convertResponse(IResponse $response): ResponseInterface {
		return new Response(
			$response->getStatusCode(),
			$response->getHeaders(),
			$response->getBody(),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	protected static function buildOptions(RequestInterface $request): array {
		return [
			'headers' => $request->getHeaders(),
			'body' => (string)$request->getBody(),
			// Push services report expired subscriptions and rate limits with error
			// status codes, they have to be inspected instead of being thrown
			'http_errors' => false,
		];
	}
}
