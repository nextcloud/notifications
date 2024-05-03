<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

/**
 * @psalm-type NotificationsNotificationAction = array{
 *     label: string,
 *     link: string,
 *     type: string,
 *     primary: bool,
 * }
 *
 * @psalm-type NotificationsNotification = array{
 *     notification_id: int,
 *     app: string,
 *     user: string,
 *     datetime: string,
 *     object_type: string,
 *     object_id: string,
 *     subject: string,
 *     message: string,
 *     link: string,
 *     actions: NotificationsNotificationAction[],
 *     subjectRich?: string,
 *     subjectRichParameters?: array<string, mixed>,
 *     messageRich?: string,
 *     messageRichParameters?: array<string, mixed>,
 *     icon?: string,
 *     shouldNotify?: bool,
 * }
 *
 * @psalm-type NotificationsPushDevice = array{
 *     publicKey: string,
 *     deviceIdentifier: string,
 *     signature: string,
 * }
 */
class ResponseDefinitions {
}
