<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

/**
 * @psalm-type NotificationsRichObjectParameter = array{
 *     type: string,
 *     id: string,
 *     name: string,
 *     server?: string,
 *     link?: string,
 *     'call-type'?: 'one2one'|'group'|'public',
 *     'icon-url'?: string,
 *     'message-id'?: string,
 *     boardname?: string,
 *     stackname?: string,
 *     size?: string,
 *     path?: string,
 *     mimetype?: string,
 *     'preview-available'?: 'yes'|'no',
 *     mtime?: string,
 *     latitude?: string,
 *     longitude?: string,
 *     description?: string,
 *     thumb?: string,
 *     website?: string,
 *     visibility?: '0'|'1',
 *     assignable?: '0'|'1',
 *     conversation?: string,
 *     etag?: string,
 *     permissions?: string,
 *     width?: string,
 *     height?: string,
 * }
 *
 * @psalm-type NotificationsRichObjectParameters = array<non-empty-string, NotificationsRichObjectParameter>
 *
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
 *     actions: list<NotificationsNotificationAction>,
 *     subjectRich?: string,
 *     subjectRichParameters?: NotificationsRichObjectParameters,
 *     messageRich?: string,
 *     messageRichParameters?: NotificationsRichObjectParameters,
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
