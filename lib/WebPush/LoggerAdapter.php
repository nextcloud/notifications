<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\WebPush;

use OCA\Notifications\Vendor\Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * The web push library is prefixed by Mozart, so it type hints the prefixed
 * copy of the PSR-3 interface which the server logger does not implement.
 * This adapter bridges the two so the library logs into the Nextcloud log
 * instead of calling trigger_error().
 */
class LoggerAdapter extends AbstractLogger {
	public function __construct(
		private readonly LoggerInterface $logger,
	) {
	}

	#[\Override]
	public function log($level, string|\Stringable $message, array $context = []): void {
		$this->logger->log($level, $message, $context + ['app' => 'notifications']);
	}
}
