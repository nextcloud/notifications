<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Notifications;

use OCA\Notifications\Exceptions\NotificationNotFoundException;
use OCA\Notification\Event\NotificationProcessed;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Notification\IDeferrableApp;
use OCP\Notification\INotification;
use Symfony\Component\Console\Output\OutputInterface;

class App implements IDeferrableApp {
	/** @var Handler */
	protected $handler;
	/** @var Push */
	protected $push;
	/** @var IEventDispatcher */
	protected $eventDispatcher;

	public function __construct(Handler $handler, Push $push, IEventDispatcher $eventDispatcher) {
		$this->handler = $handler;
		$this->push = $push;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function setOutput(OutputInterface $output): void {
		$this->push->setOutput($output);
	}

	/**
	 * @param INotification $notification
	 * @throws \InvalidArgumentException When the notification is not valid
	 * @since 8.2.0
	 */
	public function notify(INotification $notification): void {
		$notificationId = $this->handler->add($notification);

		try {
			$notificationToPush = $this->handler->getById($notificationId, $notification->getUser());
			$this->push->pushToDevice($notificationId, $notificationToPush);
		} catch (NotificationNotFoundException $e) {
			throw new \InvalidArgumentException('Error while preparing push notification');
		}
	}

	/**
	 * @param INotification $notification
	 * @return int
	 * @since 8.2.0
	 */
	public function getCount(INotification $notification): int {
		return $this->handler->count($notification);
	}

	/**
	 * @param INotification $notification
	 * @since 8.2.0
	 */
	public function markProcessed(INotification $notification): void {
		$deleted = $this->handler->delete($notification);

		if ($deleted) {
			$this->eventDispatcher->dispatchTyped(new NotificationProcessed($notification));
		}

		$isAlreadyDeferring = $this->push->isDeferring();
		if (!$isAlreadyDeferring) {
			$this->push->deferPayloads();
		}
		foreach ($deleted as $user => $notifications) {
			foreach ($notifications as $notificationId) {
				$this->push->pushDeleteToDevice($user, $notificationId);
			}
		}
		if (!$isAlreadyDeferring) {
			$this->push->flushPayloads();
		}
	}

	public function defer(): void {
		$this->push->deferPayloads();
	}

	public function flush(): void {
		$this->push->flushPayloads();
	}
}
