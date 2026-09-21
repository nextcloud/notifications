<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Service;

use OCA\Notifications\Exceptions\InvalidSnoozeException;
use OCA\Notifications\Handler;
use OCA\Notifications\Push;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Notification\INotification;

class SnoozeService {
	public const int MAX_SNOOZE = 365 * 24 * 3600;

	public function __construct(
		protected Handler $handler,
		protected Push $push,
		protected ITimeFactory $timeFactory,
	) {
	}

	/**
	 * Snooze a notification until the given timestamp and stop the push
	 * that was already delivered for it.
	 *
	 * @throws InvalidSnoozeException
	 */
	public function snooze(INotification $notification, int $id, string $user, int $snoozeUntil): void {
		$now = $this->timeFactory->getTime();
		if ($snoozeUntil <= $now || $snoozeUntil > $now + self::MAX_SNOOZE) {
			throw new InvalidSnoozeException('snoozeUntil is out of range');
		}

		$this->handler->snooze($id, $user, $snoozeUntil, $notification);
		$this->push->pushDeleteToDevice($user, [$id], $notification->getApp());
	}

	/**
	 * Re-deliver a snoozed notification as a new row and push it again.
	 * Uses a raw delete of the old row, not deleteById(), because the
	 * originating app's IDismissableNotifier already ran when it was snoozed.
	 *
	 * @return int the new notification id
	 */
	public function wakeUp(int $oldId, INotification $notification): int {
		$notification->setDateTime($this->timeFactory->getDateTime());

		$newId = $this->handler->add($notification);
		$this->handler->deleteIds([$oldId]);

		$this->push->pushToDevice($newId, $notification);

		return $newId;
	}

	/**
	 * Wake up all notifications whose snooze has expired by now
	 *
	 * @return int the number of notifications woken up
	 */
	public function wakeUpDue(int $now, int $limit): int {
		$due = $this->handler->getSnoozedUntil($now, $limit);

		$shouldFlush = !$this->push->isDeferring();
		if ($shouldFlush) {
			$this->push->deferPayloads();
		}

		foreach ($due as $oldId => $notification) {
			$this->wakeUp($oldId, $notification);
		}

		if ($shouldFlush) {
			$this->push->flushPayloads();
		}

		return count($due);
	}
}
