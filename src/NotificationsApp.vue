<template>
	<HeaderMenu v-if="!shutdown"
		id="notifications"
		class="notifications-button"
		exclude-click-outside-classes="popover"
		:open.sync="open"
		:aria-label="t('notifications', 'Notifications')"
		@open="onOpen">
		<template #trigger>
			<Bell v-if="notifications.length === 0 && webNotificationsGranted !== null && !hasThrottledPushNotifications"
				:size="20"
				:title="t('notifications', 'Notifications')"
				fill-color="var(--color-primary-text)" />
			<!-- From material design icons -->
			<svg v-else
				xmlns="http://www.w3.org/2000/svg"
				xmlns:xlink="http://www.w3.org/1999/xlink"
				version="1.1"
				width="20"
				height="20"
				viewBox="0 0 24 24"
				fill="var(--color-primary-text)">
				<path d="M 19,11.79 C 18.5,11.92 18,12 17.5,12 14.47,12 12,9.53 12,6.5 12,5.03 12.58,3.7 13.5,2.71 13.15,2.28 12.61,2 12,2 10.9,2 10,2.9 10,4 V 4.29 C 7.03,5.17 5,7.9 5,11 v 6 l -2,2 v 1 H 21 V 19 L 19,17 V 11.79 M 12,23 c 1.11,0 2,-0.89 2,-2 h -4 c 0,1.11 0.9,2 2,2 z" />
				<path :class="isRedThemed ? 'notification__dot--white' : ''" class="notification__dot" d="M 21,6.5 C 21,8.43 19.43,10 17.5,10 15.57,10 14,8.43 14,6.5 14,4.57 15.57,3 17.5,3 19.43,3 21,4.57 21,6.5" />
				<path v-if="hasThrottledPushNotifications"
					:class="isOrangeThemed ? 'notification__dot--white' : ''"
					class="notification__dot notification__dot--warning"
					d="M 21,6.5 C 21,8.43 19.43,10 17.5,10 15.57,10 14,8.43 14,6.5 14,4.57 15.57,3 17.5,3 19.43,3 21,4.57 21,6.5" />
			</svg>
		</template>

		<!-- Notifications list content -->
		<div ref="container" class="notification-container">
			<transition name="fade" mode="out-in">
				<div v-if="notifications.length > 0">
					<transition-group class="notification-wrapper"
						name="list"
						tag="ul">
						<Notification v-if="hasThrottledPushNotifications"
							:key="-2016"
							datetime="warning"
							app="core"
							:icon="warningIcon"
							external-link="https://nextcloud.com/pushnotifications"
							:message="emptyContentDescription"
							:subject="emptyContentMessage"
							:index="2016" />
						<Notification v-for="(n, index) in notifications"
							:key="n.notificationId"
							v-bind="n"
							:index="index"
							@remove="onRemove" />
					</transition-group>

					<!-- Dismiss all -->
					<span v-if="notifications.length > 0"
						class="dismiss-all"
						@click="onDismissAll">
						<NcButton type="tertiary"
							@click="onDismissAll">
							<template #icon>
								<Close :size="20" />
							</template>
							{{ t('notifications', 'Dismiss all notifications') }}
						</NcButton>
					</span>
				</div>

				<!-- No notifications -->
				<NcEmptyContent v-else
					:title="emptyContentMessage"
					:description="emptyContentDescription">
					<template #icon>
						<Bell v-if="!hasThrottledPushNotifications" />
						<span v-else class="icon icon-alert-outline" />
					</template>

					<template v-if="hasThrottledPushNotifications" #action>
						<NcButton type="primary"
							href="https://nextcloud.com/pushnotifications"
							target="_blank"
							rel="noreferrer noopener">
							<template #icon>
								<Message :size="20" />
							</template>
							{{ t('notifications', 'Contact Nextcloud GmbH') }} ↗
						</NcButton>
					</template>
				</NcEmptyContent>
			</transition>
		</div>
	</HeaderMenu>
</template>

<script>
import Notification from './Components/Notification.vue'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import Close from 'vue-material-design-icons/Close.vue'
import axios from '@nextcloud/axios'
import { subscribe, unsubscribe } from '@nextcloud/event-bus'
import { showError } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import {
	generateOcsUrl,
	imagePath,
} from '@nextcloud/router'
import { getNotificationsData } from './services/notificationsService.js'
import { listen } from '@nextcloud/notify_push'
import Bell from 'vue-material-design-icons/Bell.vue'
import Message from 'vue-material-design-icons/Message.vue'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import { getCapabilities } from '@nextcloud/capabilities'
import HeaderMenu from './Components/HeaderMenu.vue'

export default {
	name: 'NotificationsApp',

	components: {
		NcButton,
		Close,
		Bell,
		Message,
		NcEmptyContent,
		HeaderMenu,
		Notification,
	},

	data() {
		return {
			webNotificationsGranted: false,
			backgroundFetching: false,
			hasNotifyPush: false,
			shutdown: false,
			theming: getCapabilities()?.theming || {},
			hasThrottledPushNotifications: loadState('notifications', 'throttled_push_notifications'),
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

			open: false,
		}
	},

	_$icon: null,

	computed: {
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
		isOrangeThemed() {
			if (this.theming?.color) {
				const hsl = this.rgbToHsl(this.theming.color.substring(1, 3),
					this.theming.color.substring(3, 5),
					this.theming.color.substring(5, 7))
				const h = hsl[0] * 360
				return (h >= 305 || h <= 64) && hsl[1] > 0.7 && (hsl[2] > 0.1 || hsl[2] < 0.6)
			}
			return false
		},

		showBrowserNotifications() {
			return this.backgroundFetching
				&& this.webNotificationsGranted
				&& this.userStatus !== 'dnd'
				&& this.tabId === this.lastTabId
		},

		emptyContentMessage() {
			if (this.webNotificationsGranted === null) {
				return t('notifications', 'Requesting browser permissions to show notifications')
			}

			if (this.hasThrottledPushNotifications) {
				return t('notifications', 'Push notifications might be unreliable')
			}

			return t('notifications', 'No notifications')
		},

		emptyContentDescription() {
			if (this.hasThrottledPushNotifications) {
				return t('notifications', 'Nextcloud GmbH sponsors a free push notification gateway for private users. To ensure good service, the gateway limits the number of push notifications per server. For enterprise users, a more scalable gateway is available. Contact Nextcloud GmbH for more information.')
			}

			return ''
		},

		warningIcon() {
			return imagePath('core', 'actions/alert-outline.svg')
		},
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
			this.hasNotifyPush = true
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

	methods: {
		onOpen() {
			this.requestWebNotificationPermissions()
		},
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
			if (this.hasNotifyPush && this.tabId !== this.lastTabId) {
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
			const response = await getNotificationsData(this.tabId, this.lastETag, !this.backgroundFetching, this.hasNotifyPush)

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

<style scoped lang="scss">
.notification-container {
	/* Prevent slide animation to go out of the div */
	overflow: hidden;
}

::v-deep .empty-content {
	margin: 12vh 10px;

	p {
		color: var(--color-text-maxcontrast);
	}
}

.icon-alert-outline {
	background-size: 64px;
	width: 64px;
	height: 64px;
}

.fade-enter-active,
.fade-leave-active {
	transition: opacity var(--animation-quick) ease;
}

.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}

.list-move,
.list-enter-active,
.list-leave-active {
	transition: all var(--animation-quick) ease;
}

.list-enter-from,
.list-leave-to {
	opacity: 0;
	transform: translateX(30px);
}

.list-leave-active {
	width: 100%;
}
</style>
