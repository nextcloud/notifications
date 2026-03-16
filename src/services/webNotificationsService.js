/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { emit } from '@nextcloud/event-bus'
import { loadState } from '@nextcloud/initial-state'
import { generateFilePath } from '@nextcloud/router'
import { Howl } from 'howler'
import BrowserStorage from './BrowserStorage.js'

/**
 * Add primary-element background and color the icon in matching text color
 *
 * @param {string} iconUrl URL of the icon (typically black on transparent)
 * @return {Promise<string>} data URL of the themed icon
 */
function invertIconColors(iconUrl) {
	return new Promise((resolve, reject) => {
		const img = new Image()
		img.crossOrigin = 'anonymous'
		img.onload = () => {
			const canvas = document.createElement('canvas')
			const size = 32 + 4 * (Math.max(img.width, img.height) || 16)
			canvas.width = size
			canvas.height = size
			const ctx = canvas.getContext('2d')

			ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--color-primary-element')
			ctx.beginPath()
			ctx.arc(size / 2, size / 2, size / 2, 0, 2 * Math.PI)
			ctx.fill()

			// Apply invert filter when the font-color is not supposed to be black
			if (getComputedStyle(document.body).getPropertyValue('--color-primary-text') !== '#000000') {
				ctx.filter = 'invert(100%)'
			}
			const x = (size - 4 * img.width) / 2
			const y = (size - 4 * img.height) / 2
			ctx.drawImage(img, x, y, img.width * 4, img.height * 4)

			resolve(canvas.toDataURL('image/png'))
		}
		img.onerror = reject
		img.src = iconUrl
	})
}

/**
 * Create a browser notification
 *
 * @param {object} notification notification object
 * @see https://developer.mozilla.org/en/docs/Web/API/notification
 */
const createWebNotification = async (notification) => {
	if (!notification.shouldNotify) {
		return
	}

	let icon = notification.icon
	if (icon) {
		try {
			icon = await invertIconColors(icon)
		} catch (e) {
			console.info('Failed to apply coloring to notification icon, using original', e)
		}
	}

	const n = new Notification(notification.subject, {
		title: notification.subject,
		lang: OC.getLocale(),
		body: notification.message,
		icon,
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
