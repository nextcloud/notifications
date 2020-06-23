<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Notifications\Notifier;

use OCP\IL10N;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class FlowNotifications implements INotifier {
	/** @var IL10N */
	private $l;

	public const NOTIFIER_ID = 'flow_notifications';

	public function __construct(IL10N $l) {
		$this->l = $l;
	}

	/**
	 * @inheritDoc
	 */
	public function getID(): string {
		return self::NOTIFIER_ID;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return $this->l->t('Flow Notifications');
	}

	/**
	 * @inheritDoc
	 */
	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== self::NOTIFIER_ID) {
			throw new \InvalidArgumentException();
		}

		$notification
			->setParsedSubject($notification->getSubject())
			->setParsedMessage($notification->getMessage());

		return $notification;
	}
}
