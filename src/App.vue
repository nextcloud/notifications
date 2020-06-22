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
						<Notification
							v-for="(n, index) in notifications"
							:key="n.notification_id"
							v-bind="n"
							:index="index"
							:notification-id="n.notification_id"
							:object-id="n.object_id"
							:object-type="n.object_type"
							@remove="onRemove" />
					</transition-group>
					<li v-if="notifications.length > 2">
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
import { subscribe } from '@nextcloud/event-bus'
import { imagePath, generateOcsUrl } from '@nextcloud/router'

export default {
	name: 'App',

	components: {
		Notification,
	},

	data: function() {
		return {
			webNotificationsGranted: null,
			hadNotifications: false,
			backgroundFetching: false,
			shutdown: false,
			notifications: [],
			lastETag: null,

			/** @type {number} */
			pollInterval: 30000, // milliseconds

			/** @type {number|null} */
			interval: null,
		}
	},

	_$icon: null,

	computed: {
		iconPath: function() {
			let iconPath = 'notifications'

			if (this.webNotificationsGranted === null || this.notifications.length) {
				if (this.isRedThemed()) {
					iconPath += '-red'
				}
				iconPath += '-new'
			}

			if (this.invertedTheme()) {
				iconPath += '-dark'
			}

			return imagePath('notifications', iconPath)
		},
	},

	mounted: function() {
		this._$icon = $(this.$refs.icon)

		// Bind the button click event
		OC.registerMenu($(this.$refs.button), $(this.$refs.container), undefined, true)

		this.checkWebNotificationPermissions()

		// Initial call to the notification endpoint
		this._fetch()

		// Setup the background checker
		this.setupBackgroundFetcher()

		subscribe('networkOffline', () => {
			this._shutDownNotifications(true)
		})
		subscribe('networkOnline', () => {
			this._fetch()
			this.setupBackgroundFetcher()
		})
	},

	updated: function() {
		this._$icon.attr('src', this.iconPath)

		if (!this.hadNotifications && this.notifications.length) {
			this._$icon
				.animate({ opacity: 0.6 }, 600)
				.animate({ opacity: 1 }, 600)
				.animate({ opacity: 0.6 }, 600)
				.animate({ opacity: 1 }, 600)
		}

		this.hadNotifications = this.notifications.length > 0
	},

	methods: {
		setupBackgroundFetcher() {
			if (OC.config.session_keepalive) {
				this.interval = setInterval(this._backgroundFetch.bind(this), this.pollInterval)
			}
		},

		onDismissAll: function() {
			axios
				.delete(generateOcsUrl('apps/notifications/api/v2', 2) + 'notifications')
				.then(() => {
					this.notifications = []
				})
				.catch(() => {
					OC.Notification.showTemporary(t('notifications', 'Failed to dismiss all notifications'))
				})
		},
		onRemove: function(index) {
			this.notifications.splice(index, 1)
		},

		invertedTheme: function() {
			return OCA.Theming && OCA.Theming.inverted
		},

		isRedThemed: function() {
			if (OCA.Theming && OCA.Theming.color) {
				const hsl = this.rgbToHsl(OCA.Theming.color.substring(1, 3),
					OCA.Theming.color.substring(3, 5),
					OCA.Theming.color.substring(5, 7))
				const h = hsl[0] * 360
				return (h >= 330 || h <= 15) && hsl[1] > 0.7 && (hsl[2] > 0.1 || hsl[2] < 0.6)
			}
			return false
		},

		rgbToHsl: function(r, g, b) {
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
			 * Performs the AJAX request to retrieve the notifications
			 */
		_fetch: function() {
			let requestConfig = {}
			if (this.lastETag) {
				requestConfig = {
					headers: {
						'If-None-Match': this.lastETag,
					},
				}
			}

			axios
				.get(generateOcsUrl('apps/notifications/api/v2', 2) + 'notifications', requestConfig)
				.then(response => {
					if (response.status === 204) {
						// 204 No Content - Intercept when no notifiers are there.
						this._setPollingInterval(300000)
						return
					} else if (response.data !== undefined && response.data.ocs !== undefined && response.data.ocs.data !== undefined && Array.isArray(response.data.ocs.data)) {
						this.lastETag = response.headers.etag
						this.notifications = response.data.ocs.data
					} else {
						console.info('data.ocs.data is undefined or not an array')
					}

					this._setPollingInterval(30000)
				})
				.catch(err => {
					if (!err.response) {
						console.info('No response received, retrying')
						return
					} else if (err.response.status === 304) {
						// 304 - Not modified
						return
					} else if (err.response.status === 503) {
						// 503 - Maintenance mode
						console.info('Slowing down notifications: instance is in maintenance mode.')
					} else if (err.response.status === 404) {
						// 404 - App disabled
						console.info('Slowing down notifications: app is disabled.')
					} else {
						console.info('Slowing down notifications: [' + err.response.status + '] ' + err.response.statusText)
					}

					this._setPollingInterval(300000)
				})
		},

		_backgroundFetch: function() {
			this.backgroundFetching = true
			this._fetch()
		},

		_setPollingInterval(pollInterval) {
			if (pollInterval === this.pollInterval) {
				return
			}

			if (this.interval) {
				window.clearInterval(this.interval)
				this.interval = null
			}

			this.pollInterval = pollInterval
			this.setupBackgroundFetcher()
		},

		/**
		 * The app was disabled or has no notifiers, so we can stop polling
		 * And hide the UI as well
		 * @param {Boolean} temporary If false, the notification bell will be hidden
		 */
		_shutDownNotifications: function(temporary) {
			if (this.interval) {
				window.clearInterval(this.interval)
				this.interval = null
			}
			this.shutdown = !temporary
		},

		/**
		 * Check if we can do web notifications
		 */
		checkWebNotificationPermissions: function() {
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

			console.info('Notifications permissions not yet requested')
		},

		/**
		 * Check if we can do web notifications
		 */
		requestWebNotificationPermissions: async function() {
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
