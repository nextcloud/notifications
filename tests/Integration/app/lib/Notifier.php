<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\NotificationsIntegrationTesting;

use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\UnknownNotificationException;

class Notifier implements INotifier {
	public function getID(): string {
		return 'notificationsintegrationtesting';
	}

	public function getName(): string {
		return 'Integration test';
	}

	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() === 'notificationsintegrationtesting') {
			$notification->setParsedSubject($notification->getSubject());
			$notification->setParsedMessage($notification->getMessage());
			return $notification;
		}

		throw new UnknownNotificationException();
	}
}
