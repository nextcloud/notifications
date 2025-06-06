<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Notifications;

use OCA\Notifications\Exceptions\NotificationNotFoundException;
use OCP\Notification\IDeferrableApp;
use OCP\Notification\INotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class App implements IDeferrableApp {
	protected ?int $lastInsertedId = null;
	public function __construct(
		protected Handler $handler,
		protected Push $push,
		protected LoggerInterface $logger,
	) {
	}

	public function setOutput(OutputInterface $output): void {
		$this->push->setOutput($output);
	}

	#[\Override]
	public function notify(INotification $notification): void {
		$this->lastInsertedId = $this->handler->add($notification);

		try {
			$this->push->pushToDevice($this->lastInsertedId, $notification);
		} catch (NotificationNotFoundException $e) {
			$this->logger->error('Error while preparing push notification', ['exception' => $e]);
		}
	}

	public function getLastInsertedId(): ?int {
		return $this->lastInsertedId;
	}

	#[\Override]
	public function getCount(INotification $notification): int {
		return $this->handler->count($notification);
	}

	#[\Override]
	public function markProcessed(INotification $notification): void {
		$deleted = $this->handler->delete($notification);

		$isAlreadyDeferring = $this->push->isDeferring();
		if (!$isAlreadyDeferring) {
			$this->push->deferPayloads();
		}
		foreach ($deleted as $user => $notifications) {
			foreach ($notifications as $data) {
				$this->push->pushDeleteToDevice((string)$user, [$data['id']], $data['app']);
			}
		}
		if (!$isAlreadyDeferring) {
			$this->push->flushPayloads();
		}
	}

	#[\Override]
	public function defer(): void {
		$this->push->deferPayloads();
	}

	#[\Override]
	public function flush(): void {
		$this->push->flushPayloads();
	}
}
