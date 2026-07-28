<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

use OCA\Notifications\Vendor\GuzzleHttp\Promise\PromiseInterface;
use OCP\Http\Client\IPromise;
use Psr\Log\LoggerInterface;

/**
 * Collects the pending push notification requests and decides when to wait
 * for them.
 *
 * On the command line the requests are sent right away, so the output of e.g.
 * `occ notification:test-push` stays in order and a cron run does not pile up
 * the push traffic of all jobs at the very end.
 *
 * For web requests the sending is postponed to the shutdown of the request.
 * When the SAPI supports it, the response is sent to the client before the
 * push notifications are sent, so the user does not wait for the push
 * endpoints to answer.
 */
class DeferredFlusher {
	/**
	 * Guzzle promises of the web push requests and IPromises of the requests
	 * to the push proxies. The two HTTP stacks are separate, so they can not
	 * run at the same time, but both have a wait() method.
	 *
	 * @var list<PromiseInterface|IPromise>
	 */
	protected array $promises = [];

	protected bool $isShutdownFunctionRegistered = false;

	public function __construct(
		protected LoggerInterface $logger,
		protected bool $isCLI,
	) {
	}

	/**
	 * @param list<PromiseInterface|IPromise> $promises
	 */
	public function add(array $promises): void {
		foreach ($promises as $promise) {
			$this->promises[] = $promise;
		}
	}

	/**
	 * Make sure the added promises are waited for, either right away on the
	 * command line, or at the end of the request otherwise.
	 */
	public function schedule(): void {
		if ($this->isCLI) {
			$this->flushNow();
			return;
		}

		if ($this->isShutdownFunctionRegistered) {
			return;
		}
		$this->isShutdownFunctionRegistered = true;
		$this->registerShutdownFunction($this->onShutdown(...));
	}

	/**
	 * Wait for all pending requests to finish
	 */
	public function flushNow(): void {
		$promises = $this->promises;
		$this->promises = [];

		foreach ($promises as $promise) {
			try {
				// Rejections are already handled by the callbacks of the
				// requests, so the result must not be unwrapped as that would
				// rethrow the exception and skip the remaining requests.
				$promise->wait(false);
			} catch (\Throwable $e) {
				$this->logger->error('Error while sending push notifications: ' . $e->getMessage(), ['exception' => $e]);
			}
		}
	}

	protected function onShutdown(): void {
		$this->finishRequest();

		// Flushing the response does not reset the execution timer, so a slow
		// push endpoint could kill the process in the middle of the sending.
		@set_time_limit(0);

		try {
			$this->flushNow();
		} catch (\Throwable $e) {
			// An exception escaping a shutdown function is close to
			// impossible to attribute, so nothing may leave this method
			$this->logger->error('Error while sending deferred push notifications: ' . $e->getMessage(), ['exception' => $e]);
		}
	}

	/**
	 * Send the response to the client, so it does not have to wait for the
	 * push notifications. Only PHP-FPM and LiteSpeed can do this, with e.g.
	 * mod_php the connection stays open until the sending is done.
	 */
	protected function finishRequest(): void {
		if (function_exists('fastcgi_finish_request')) {
			fastcgi_finish_request();
		} elseif (function_exists('litespeed_finish_request')) {
			litespeed_finish_request();
		}
	}

	protected function registerShutdownFunction(callable $callback): void {
		register_shutdown_function($callback);
	}
}
