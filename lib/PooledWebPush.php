<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

use OCA\Notifications\Vendor\GuzzleHttp\Pool;
use OCA\Notifications\Vendor\GuzzleHttp\Promise\PromiseInterface;
use OCA\Notifications\Vendor\Minishlink\WebPush\MessageSentReport;
use OCA\Notifications\Vendor\Minishlink\WebPush\WebPush;
use OCA\Notifications\Vendor\Psr\Http\Message\RequestInterface;
use OCA\Notifications\Vendor\Psr\Http\Message\ResponseInterface;

/**
 * Extends the Mozart prefixed copy of minishlink/web-push (v10.1.0) to send
 * the queued notifications without blocking on the result.
 *
 * This depends on WebPush::$notifications, ::$client, ::$defaultOptions,
 * ::$vapidHeaders, ::prepare() and ::createRejectedReport() staying protected,
 * so it has to be re-checked whenever the library is bumped.
 */
class PooledWebPush extends WebPush {
	/**
	 * Same as flushPooled(), but returns the promises of the pools instead of
	 * waiting for them, and prepares (encrypts) each request only when the
	 * pool is ready to send it, so the encryption of the next payload happens
	 * while the previous requests are in flight.
	 *
	 * @param callable(MessageSentReport): void $callback Callback for each notification
	 * @param null|int $batchSize Defaults to the value defined in defaultOptions during instantiation
	 * @param null|int $requestConcurrency Defaults to the value defined in defaultOptions during instantiation
	 * @return list<PromiseInterface> One promise per batch, the caller has to wait for them
	 */
	public function flushPooledAsync(callable $callback, ?int $batchSize = null, ?int $requestConcurrency = null): array {
		if (empty($this->notifications)) {
			return [];
		}

		$batchSize ??= $this->defaultOptions['batchSize'];
		$requestConcurrency ??= $this->defaultOptions['requestConcurrency'];

		$batches = array_chunk($this->notifications, $batchSize);
		// Reset the queue
		$this->notifications = [];

		$promises = [];
		foreach ($batches as $batch) {
			/**
			 * Filled while the generator is consumed. The fulfilled callback
			 * needs the request back and can not index into a prepared array
			 * anymore, as the requests are only created on demand now.
			 * @var array<int, RequestInterface> $prepared
			 */
			$prepared = [];

			$requests = function () use ($batch, &$prepared): \Generator {
				foreach ($batch as $index => $notification) {
					try {
						$request = $this->prepare([$notification])[0] ?? null;
					} catch (\Throwable $e) {
						// A single subscription that can not be encrypted must
						// not abort the requests of all the other subscriptions
						$this->logger?->error('Failed to prepare push notification: ' . $e->getMessage(), ['exception' => $e]);
						continue;
					}

					if ($request === null) {
						continue;
					}

					$prepared[$index] = $request;
					yield $index => $request;
				}
			};

			$pool = new Pool($this->client, $requests(), [
				'concurrency' => $requestConcurrency,
				'fulfilled' => function (ResponseInterface $response, int $index) use ($callback, &$prepared): void {
					$callback(new MessageSentReport($prepared[$index], $response));
				},
				'rejected' => function ($reason) use ($callback): void {
					$callback($this->createRejectedReport($reason));
				},
			]);

			$promises[] = $pool->promise();
		}

		return $promises;
	}

	/**
	 * Reset the cache of reused VAPID headers.
	 *
	 * flushPooled() does this after waiting for the requests. As
	 * flushPooledAsync() does not wait, the cache is instead reset before the
	 * requests of a flush are prepared, which gives the same result: the
	 * headers are only reused within one flush.
	 */
	public function clearVAPIDHeaderCache(): void {
		$this->vapidHeaders = [];
	}
}
