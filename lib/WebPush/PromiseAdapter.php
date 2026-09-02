<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\WebPush;

use OCA\Notifications\Vendor\Http\Promise\Promise;
use OCA\Notifications\Vendor\Psr\Http\Message\ResponseInterface;
use OCP\Http\Client\IPromise;
use OCP\Http\Client\IResponse;

/**
 * Adapts a promise of the Nextcloud HTTP client to the HTTPlug promise the
 * vendored web-push library works with.
 */
class PromiseAdapter implements Promise {
	private ?ResponseInterface $response = null;
	private ?\Throwable $reason = null;
	private bool $settled = false;
	/** @var list<callable(ResponseInterface): void> */
	private array $onFulfilled = [];
	/** @var list<callable(\Throwable): void> */
	private array $onRejected = [];

	public function __construct(
		protected IPromise $promise,
	) {
		$this->promise->then(function (IResponse $response): void {
			$this->response = ClientAdapter::convertResponse($response);
		});
	}

	#[\Override]
	public function then(?callable $onFulfilled = null, ?callable $onRejected = null): Promise {
		if ($onFulfilled !== null) {
			$this->onFulfilled[] = $onFulfilled;
		}
		if ($onRejected !== null) {
			$this->onRejected[] = $onRejected;
		}

		return $this;
	}

	#[\Override]
	public function getState(): string {
		return match ($this->promise->getState()) {
			IPromise::STATE_FULFILLED => Promise::FULFILLED,
			IPromise::STATE_REJECTED => Promise::REJECTED,
			default => Promise::PENDING,
		};
	}

	/**
	 * @param bool $unwrap
	 * @return ?ResponseInterface
	 * @throws \Throwable When the request failed and no failure callback was registered
	 */
	#[\Override]
	public function wait($unwrap = true) {
		$this->settle();

		if (!$unwrap) {
			return null;
		}

		if ($this->reason !== null && $this->onRejected === []) {
			throw $this->reason;
		}

		return $this->response;
	}

	/**
	 * The failure reason is taken from the awaited promise instead of from a
	 * rejection callback, as the callback of the Nextcloud client only receives
	 * request exceptions and would miss connection errors.
	 */
	private function settle(): void {
		if ($this->settled) {
			return;
		}
		$this->settled = true;

		try {
			$this->promise->wait();
		} catch (\Throwable $e) {
			$this->reason = $e;
		}

		if ($this->response !== null) {
			foreach ($this->onFulfilled as $callback) {
				$callback($this->response);
			}
			return;
		}

		$reason = $this->reason ?? new ClientException('The request did not return a response');
		foreach ($this->onRejected as $callback) {
			$callback($reason);
		}
	}
}
