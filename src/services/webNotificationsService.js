/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { emit } from '@nextcloud/event-bus'
import { loadState } from '@nextcloud/initial-state'
import { getLanguage } from '@nextcloud/l10n'
import { generateFilePath } from '@nextcloud/router'
import { Howl } from 'howler'
import BrowserStorage from './BrowserStorage.js'

/**
 * Create a browser notification
 *
 * @param {object} notification notification object
 * @see https://developer.mozilla.org/en/docs/Web/API/notification
 */
function createWebNotification(notification) {
	if (!notification.shouldNotify) {
		return
	}

	const n = new Notification(notification.subject, {
		title: notification.subject,
		lang: getLanguage(),
		body: notification.message,
		icon: notification.icon,
		tag: notification.notificationId,
	})

	if (notification.link) {
		n.onclick = async function() {
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
 *
 * @param {object} notification notification object
 */
function playNotificationSound(notification) {
	if (notification.app === 'spreed' && notification.objectType === 'call') {
		if (loadState('notifications', 'sound_talk')) {
			const howlPayload = {
				src: [generateFilePath('notifications', 'img', 'talk.ogg')],
				html5: true, // to access HTMLAudioElement property 'sinkId'
				volume: 0.5,
			}
			const sound = new Howl(howlPayload)
			const primaryDeviceId = sound._sounds[0]._node.sinkId ?? ''
			sound.play()

			const secondarySpeakerEnabled = BrowserStorage.getItem('secondary_speaker') === 'true'
			const secondaryDeviceId = JSON.parse(BrowserStorage.getItem('secondary_speaker_device'))?.id ?? null
			// Play only if secondary device is enabled, selected and different from primary device
			if (secondarySpeakerEnabled && secondaryDeviceId && primaryDeviceId !== secondaryDeviceId) {
				const soundDuped = new Howl(howlPayload)
				const audioElement = sound._sounds[0]._node // Access the underlying HTMLAudioElement
				audioElement.setSinkId?.(secondaryDeviceId)
					.then(() => console.debug('Audio output successfully redirected to secondary speaker'))
					.catch((error) => console.error('Failed to redirect audio output:', error))
				soundDuped.play()
			}
		}
	} else if (loadState('notifications', 'sound_notification')) {
		const sound = new Howl({
			src: [generateFilePath('notifications', 'img', 'notification.ogg')],
			volume: 0.5,
		})

		sound.play()
	}
}

export {
	createWebNotification,
}
