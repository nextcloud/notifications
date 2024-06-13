/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { emit } from '@nextcloud/event-bus'
import { loadState } from '@nextcloud/initial-state'
import { generateFilePath } from '@nextcloud/router'
import { Howl } from 'howler'

/**
 * Create a browser notification
 *
 * @param {object} notification notification object
 * @see https://developer.mozilla.org/en/docs/Web/API/notification
 */
const createWebNotification = (notification) => {
	if (!notification.shouldNotify) {
		return
	}

	const n = new Notification(notification.subject, {
		title: notification.subject,
		lang: OC.getLocale(),
		body: notification.message,
		icon: notification.icon,
		tag: notification.notificationId,
	})

	if (notification.link) {
		n.onclick = async function(e) {
			const event = {
				cancelAction: false,
				notification,
				action: {
					url: notification.link,
					type: 'WEB',
				},
			}
			await emit('notifications:action:execute', event)

			if (!event.cancelAction) {
				console.debug('Redirecting because of a click onto a notification', notification.link)
				window.location.href = notification.link
			}

			// Best effort try to bring the tab to the foreground (works at least in Chrome, not in Firefox)
			window.focus()
		}
	}

	playNotificationSound(notification)
}

/**
 * Play a notification sound (if enabled on instance)
 * @param {object} notification notification object
 */
const playNotificationSound = (notification) => {
	if (notification.app === 'spreed' && notification.objectType === 'call') {
		if (loadState('notifications', 'sound_talk')) {
			const sound = new Howl({
				src: [
					generateFilePath('notifications', 'img', 'talk.ogg'),
				],
				volume: 0.5,
			})

			sound.play()
		}
	} else if (loadState('notifications', 'sound_notification')) {
		const sound = new Howl({
			src: [
				generateFilePath('notifications', 'img', 'notification.ogg'),
			],
			volume: 0.5,
		})

		sound.play()
	}
}

export {
	createWebNotification,
}
