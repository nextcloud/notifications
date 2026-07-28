<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit;

use OCA\Notifications\DeferredFlusher;
use OCP\Http\Client\IPromise;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * Captures the shutdown function instead of registering it, so the test can
 * run it on demand
 */
class TestingDeferredFlusher extends DeferredFlusher {
	public int $registeredShutdownFunctions = 0;
	public ?\Closure $shutdownFunction = null;

	#[\Override]
	protected function registerShutdownFunction(callable $callback): void {
		$this->registeredShutdownFunctions++;
		$this->shutdownFunction = $callback(...);
	}
}

class DeferredFlusherTest extends TestCase {
	protected LoggerInterface&MockObject $logger;

	protected function setUp(): void {
		parent::setUp();
		$this->logger = $this->createMock(LoggerInterface::class);
	}

	/**
	 * @param int $waited Counts how often the promise was waited for
	 */
	protected function createPromise(int &$waited, ?\Throwable $throw = null): IPromise&MockObject {
		$promise = $this->createMock(IPromise::class);
		$promise->method('wait')
			->willReturnCallback(function (bool $unwrap = true) use (&$waited, $throw): mixed {
				$waited++;
				// The rejections are handled by the callbacks of the requests
				$this->assertFalse($unwrap, 'The result must not be unwrapped');
				if ($throw !== null) {
					throw $throw;
				}
				return null;
			});

		return $promise;
	}

	public function testCommandLineSendsRightAway(): void {
		$flusher = new TestingDeferredFlusher($this->logger, true);

		$waited = 0;
		$flusher->add([$this->createPromise($waited)]);
		$flusher->schedule();

		$this->assertSame(1, $waited);
		$this->assertSame(0, $flusher->registeredShutdownFunctions);
	}

	public function testWebRequestSendsOnShutdown(): void {
		$flusher = new TestingDeferredFlusher($this->logger, false);

		$waited = 0;
		$flusher->add([$this->createPromise($waited)]);
		$flusher->schedule();
		$flusher->add([$this->createPromise($waited)]);
		// Scheduling again must not register a second shutdown function
		$flusher->schedule();

		$this->assertSame(0, $waited, 'The request must not wait for the push notifications');
		$this->assertSame(1, $flusher->registeredShutdownFunctions);

		($flusher->shutdownFunction)();

		$this->assertSame(2, $waited);
	}

	public function testPromisesAreOnlyWaitedForOnce(): void {
		$flusher = new TestingDeferredFlusher($this->logger, false);

		$waited = 0;
		$flusher->add([$this->createPromise($waited)]);
		$flusher->schedule();

		($flusher->shutdownFunction)();
		($flusher->shutdownFunction)();

		$this->assertSame(1, $waited);
	}

	public function testFailingRequestDoesNotSkipTheOthers(): void {
		$flusher = new TestingDeferredFlusher($this->logger, true);

		$this->logger->expects($this->once())
			->method('error');

		$failing = 0;
		$succeeding = 0;
		$flusher->add([
			$this->createPromise($failing, new \RuntimeException('Nope')),
			$this->createPromise($succeeding),
		]);
		$flusher->schedule();

		$this->assertSame(1, $failing);
		$this->assertSame(1, $succeeding);
	}
}
