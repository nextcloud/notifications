<template>
	<div v-if="!shutdown" class="notifications">
		<div ref="button"
			class="notifications-button menutoggle"
			:class="{ hasNotifications: notifications.length }"
			tabindex="0"
			role="button"
			:aria-label="t('notifications', 'Notifications')"
			aria-haspopup="true"
			aria-controls="notification-container"
			aria-expanded="false"
			@click="requestWebNotificationPermissions">
			<img ref="icon"
				class="svg"
				alt=""
				:title="t('notifications', 'Notifications')"
				:src="iconPath">
		</div>
		<div ref="container" class="notification-container">
			<transition name="fade">
				<ul v-if="notifications.length > 0" class="notification-wrapper">
					<transition-group name="fade-collapse" tag="li">
						<Notification v-for="(n, index) in notifications"
							:key="n.notification_id"
							v-bind="n"
							:index="index"
							:notification-id="n.notification_id"
							:object-id="n.object_id"
							:object-type="n.object_type"
							@remove="onRemove" />
					</transition-group>
					<li v-if="notifications.length > 0">
						<div class="dismiss-all" @click="onDismissAll">
							<span class="icon icon-close svg" :title="t('notifications', 'Dismiss all notifications')" /> {{ t('notifications', 'Dismiss all notifications') }}
						</div>
					</li>
				</ul>
				<div v-else class="emptycontent">
					<div class="icon icon-notifications-dark" />
					<h2 v-if="webNotificationsGranted === null">
						{{ t('notifications', 'Requesting browser permissions to show notifications') }}
					</h2>
					<h2 v-else>
						{{ t('notifications', 'No notifications') }}
					</h2>
				</div>
			</transition>
		</div>
	</div>
</template>

<script>
import Notification from './Components/Notification'
import axios from '@nextcloud/axios'
import { subscribe, unsubscribe } from '@nextcloud/event-bus'
import { loadState } from '@nextcloud/initial-state'
import { showError } from '@nextcloud/dialogs'
import { imagePath, generateOcsUrl } from '@nextcloud/router'
import { getNotificationsData } from './services/notificationsService'
import { listen } from '@nextcloud/notify_push'

export default {
	name: 'App',

	components: {
		Notification,
	},

	data() {
		return {
			webNotificationsGranted: false,
			hadNotifications: false,
			backgroundFetching: false,
			shutdown: false,
			theming: {},
			notifications: [],
			lastETag: null,
			lastTabId: null,
			userStatus: null,
			tabId: null,

			/** @type {number} */
			pollIntervalBase: 30000, // milliseconds
			/** @type {number} */
			pollIntervalCurrent: 30000, // milliseconds

			/** @type {number|null} */
			interval: null,
			pushEndpoints: null,
		}
	},

	_$icon: null,

	computed: {
		iconPath() {
			let iconPath = 'notifications'

			if (this.webNotificationsGranted === null || this.notifications.length) {
				if (this.isRedThemed) {
					iconPath += '-red'
				}
				iconPath += '-new'
			}

			if (this.invertedTheme) {
				iconPath += '-dark'
			}

			return imagePath('notifications', iconPath)
		},

		invertedTheme() {
			return !!this.theming?.inverted
		},

		isRedThemed() {
			if (this.theming?.color) {
				const hsl = this.rgbToHsl(this.theming.color.substring(1, 3),
					this.theming.color.substring(3, 5),
					this.theming.color.substring(5, 7))
				const h = hsl[0] * 360
				return (h >= 330 || h <= 15) && hsl[1] > 0.7 && (hsl[2] > 0.1 || hsl[2] < 0.6)
			}
			return false
		},

		showBrowserNotifications() {
			return this.backgroundFetching
				&& this.webNotificationsGranted
				&& this.userStatus !== 'dnd'
				&& this.tabId === this.lastTabId
		},
	},

	beforeMount() {
		console.debug('Loading theming data for notification bell styling')
		this.theming = loadState('theming', 'data', {})
	},

	mounted() {
		this.tabId = OC.requestToken || ('' + Math.random())
		this._$icon = $(this.$refs.icon)
		this._oldcount = 0

		// Bind the button click event
		console.debug('Registering notifications container as a menu')
		OC.registerMenu($(this.$refs.button), $(this.$refs.container), undefined, true)

		this.checkWebNotificationPermissions()

		// Initial call to the notification endpoint
		this._fetch()

		const hasPush = listen('notify_notification', () => {
			this._fetchAfterNotifyPush()
		})
		if (hasPush) {
			console.debug('Has notify_push enabled, slowing polling to 15 minutes')
			this.pollIntervalBase = 15 * 60 * 1000
		}

		// Setup the background checker
		this._setPollingInterval(this.pollIntervalBase)

		this._watchTabVisibility()
		subscribe('networkOffline', this.handleNetworkOffline)
		subscribe('networkOnline', this.handleNetworkOnline)
	},

	beforeDestroy() {
		unsubscribe('networkOffline', this.handleNetworkOffline)
		unsubscribe('networkOnline', this.handleNetworkOnline)
	},

	updated() {
		if (!this.hadNotifications && this.notifications.length) {
			console.debug('New notification, animating the bell icon')
			this._$icon
				.animate({ opacity: 0.6 }, 600)
				.animate({ opacity: 1 }, 600)
				.animate({ opacity: 0.6 }, 600)
				.animate({ opacity: 1 }, 600)
		}

		this.hadNotifications = this.notifications.length > 0
	},

	methods: {
		handleNetworkOffline() {
			console.debug('Network is offline, slowing down pollingInterval to ' + this.pollIntervalBase * 10)
			this._setPollingInterval(this.pollIntervalBase * 10)
		},

		handleNetworkOnline() {
			this._fetch()
			console.debug('Network is online, reseting pollingInterval to ' + this.pollIntervalBase)
			this._setPollingInterval(this.pollIntervalBase)
		},

		setupBackgroundFetcher() {
			if (OC.config.session_keepalive) {
				console.debug('Started background fetcher as session_keepalive is enabled')
				this.interval = window.setInterval(this._backgroundFetch.bind(this), this.pollIntervalCurrent)
			} else {
				console.debug('Did not start background fetcher as session_keepalive is off')
			}
		},

		onDismissAll() {
			axios
				.delete(generateOcsUrl('apps/notifications/api/v2/notifications'))
				.then(() => {
					this.notifications = []
				})
				.catch(() => {
					showError(t('notifications', 'Failed to dismiss all notifications'))
				})
		},
		onRemove(index) {
			this.notifications.splice(index, 1)
		},

		rgbToHsl(r, g, b) {
			r = parseInt(r, 16) / 255; g = parseInt(g, 16) / 255; b = parseInt(b, 16) / 255
			const max = Math.max(r, g, b); const min = Math.min(r, g, b)
			let h; let s; const l = (max + min) / 2

			if (max === min) {
				h = s = 0
			} else {
				const d = max - min
				s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
				switch (max) {
				case r: h = (g - b) / d + (g < b ? 6 : 0); break
				case g: h = (b - r) / d + 2; break
				case b: h = (r - g) / d + 4; break
				}
				h /= 6
			}

			return [h, s, l]
		},

		/**
		 * Update the title to show * if there are new notifications
		 *
		 * @param {object} notifications The list of notifications
		 */
		_updateDocTitleOnNewNotifications(notifications) {
			if (notifications.length > this._oldcount) {
				this._oldcount = notifications.length
				if (this.backgroundFetching && document.hidden) {
					// If we didn't already highlight, store the title so we can restore on tab-view
					if (!document.title.startsWith('* ')) {
						document.title = '* ' + document.title
					}
				}
			}
		},

		/**
		 * Restore the title to remove a *
		 * Only restore title if it's still what we set it to,
		 * the Talk might have altered it.
		 */
		_restoreTitle() {
			if (document.title.startsWith('* ')) {
				document.title = document.title.substring(2)
			}
		},

		/**
		 * Performs the AJAX request to retrieve the notifications
		 */
		_fetchAfterNotifyPush() {
			this.backgroundFetching = true
			if (this.notifyPush && this.tabId !== this.lastTabId) {
				console.debug('Deferring notification refresh from browser storage are notify_push event to give the last tab the chance to do it')
				setTimeout(() => {
					this._fetch()
				}, 5000)
			} else {
				console.debug('Refreshing notifications are notify_push event')
				this._fetch()
			}
		},

		/**
		 * Performs the AJAX request to retrieve the notifications
		 */
		async _fetch() {
			const response = await getNotificationsData(this.tabId, this.lastETag, !this.backgroundFetching)

			if (response.status === 204) {
				// 204 No Content - Intercept when no notifiers are there.
				console.debug('Fetching notifications but no content, slowing down polling to ' + this.pollIntervalBase * 10)
				this._setPollingInterval(this.pollIntervalBase * 10)
			} else if (response.status === 200) {
				this.userStatus = response.headers['x-nextcloud-user-status']
				this.lastETag = response.headers.etag
				this.lastTabId = response.tabId
				this.notifications = response.data
				console.debug('Got notification data')
				this._setPollingInterval(this.pollIntervalBase)
				this._updateDocTitleOnNewNotifications(this.notifications)
			} else if (response.status === 304) {
				// 304 - Not modified
				console.debug('No new notification data received')
				this._setPollingInterval(this.pollIntervalBase)
			} else if (response.status === 503) {
				// 503 - Maintenance mode
				console.info('Slowing down notifications: instance is in maintenance mode.')
				this._setPollingInterval(this.pollIntervalBase * 10)
			} else if (response.status === 404) {
				// 404 - App disabled
				console.info('Slowing down notifications: app is disabled.')
				this._setPollingInterval(this.pollIntervalBase * 10)
			} else {
				console.info('Slowing down notifications: Status ' + response.status)
				this._setPollingInterval(this.pollIntervalBase * 10)
			}
		},

		_backgroundFetch() {
			this.backgroundFetching = true
			this._fetch()
		},

		_watchTabVisibility() {
			document.addEventListener('visibilitychange', this._visibilityChange, false)
		},

		_visibilityChange() {
			if (!document.hidden) {
				this._restoreTitle()
			}
		},

		_setPollingInterval(pollInterval) {
			console.debug('Polling interval updated to ' + pollInterval)
			if (this.interval && pollInterval === this.pollIntervalCurrent) {
				return
			}

			if (this.interval) {
				window.clearInterval(this.interval)
				this.interval = null
			}

			this.pollIntervalCurrent = pollInterval
			this.setupBackgroundFetcher()
		},

		/**
		 * The app was disabled or has no notifiers, so we can stop polling
		 * And hide the UI as well
		 *
		 * @param {boolean} temporary If false, the notification bell will be hidden
		 */
		_shutDownNotifications(temporary) {
			console.debug('Shutting down notifications ' + ((temporary) ? 'temporary' : 'bye'))
			if (this.interval) {
				window.clearInterval(this.interval)
				this.interval = null
			}
			this.shutdown = !temporary
		},

		/**
		 * Check if we can do web notifications
		 */
		checkWebNotificationPermissions() {
			if (!('Notification' in window)) {
				console.info('Browser does not support notifications')
				this.webNotificationsGranted = false
				return
			}

			if (window.Notification.permission === 'granted') {
				console.debug('Notifications permissions granted')
				this.webNotificationsGranted = true
				return
			}

			if (window.Notification.permission === 'denied') {
				console.debug('Notifications permissions denied')
				this.webNotificationsGranted = false
				return
			}

			if (window.location.protocol === 'http:') {
				console.debug('Notifications require HTTPS')
				this.webNotificationsGranted = false
				return
			}

			console.info('Notifications permissions not yet requested')
			this.webNotificationsGranted = null
		},

		/**
		 * Check if we can do web notifications
		 */
		async requestWebNotificationPermissions() {
			if (this.webNotificationsGranted !== null) {
				return
			}

			console.info('Requesting notifications permissions')
			window.Notification.requestPermission()
				.then((permissions) => {
					this.webNotificationsGranted = permissions === 'granted'
				})
		},
	},
}
</script>

<style scoped>
	.fade-enter-active,
	.fade-leave-active,
	.fade-collapse-enter-active,
	.fade-collapse-leave-active {
		transition: opacity var(--animation-quick), max-height var(--animation-quick);
	}

	.fade-collapse-enter,
	.fade-collapse-leave-to {
		opacity: 0;
		max-height: 0;
	}

	.fade-enter,
	.fade-leave-to {
		opacity: 0;
	}
</style>
