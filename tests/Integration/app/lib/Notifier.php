<?php
/**
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\NotificationsIntegrationTesting;

use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {
	/**
	 * Identifier of the notifier, only use [a-z0-9_]
	 *
	 * @return string
	 * @since 17.0.0
	 */
	public function getID(): string {
		return 'notificationsintegrationtesting';
	}

	/**
	 * Human readable name describing the notifier
	 *
	 * @return string
	 * @since 17.0.0
	 */
	public function getName(): string {
		return 'Integration test';
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 * @return INotification
	 * @throws \InvalidArgumentException When the notification was not prepared by a notifier
	 */
	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() === 'notificationsintegrationtesting') {
			$notification->setParsedSubject($notification->getSubject());
			$notification->setParsedMessage($notification->getMessage());
			return $notification;
		}

		throw new \InvalidArgumentException();
	}
}
