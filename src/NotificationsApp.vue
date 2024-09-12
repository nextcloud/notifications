<template>
	<NcHeaderMenu v-if="!shutdown"
		id="notifications"
		class="notifications-button"
		:exclude-click-outside-selectors="['.popover']"
		:open.sync="open"
		:aria-label="t('notifications', 'Notifications')"
		@open="onOpen">
		<template #trigger>
			<IconNotification :show-dot="notifications.length !== 0 || webNotificationsGranted === null"
				:show-warning="hasThrottledPushNotifications" />
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
							external-link="https://nextcloud.com/fairusepolicy"
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
								<IconClose :size="20" />
							</template>
							{{ t('notifications', 'Dismiss all notifications') }}
						</NcButton>
					</span>
				</div>

				<!-- No notifications -->
				<NcEmptyContent v-else
					:name="emptyContentMessage"
					:description="emptyContentDescription">
					<template #icon>
						<IconBell v-if="!hasThrottledPushNotifications" />
						<span v-else class="icon icon-alert-outline" />
					</template>

					<template v-if="hasThrottledPushNotifications" #action>
						<NcButton type="primary"
							href="https://nextcloud.com/fairusepolicy"
							target="_blank"
							rel="noreferrer noopener">
							<template #icon>
								<IconMessage :size="20" />
							</template>
							{{ t('notifications', 'Contact Nextcloud GmbH') }} ↗
						</NcButton>
					</template>
				</NcEmptyContent>
			</transition>
		</div>
	</NcHeaderMenu>
</template>

<script>
import IconBell from 'vue-material-design-icons/Bell.vue'
import IconClose from 'vue-material-design-icons/Close.vue'
import IconMessage from 'vue-material-design-icons/Message.vue'
import IconNotification from './Components/IconNotification.vue'
import Notification from './Components/Notification.vue'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import axios from '@nextcloud/axios'
import { getCurrentUser } from '@nextcloud/auth'
import { emit, subscribe, unsubscribe } from '@nextcloud/event-bus'
import { showError } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import {
	generateOcsUrl,
	imagePath,
} from '@nextcloud/router'
import { getNotificationsData } from './services/notificationsService.js'
import { listen } from '@nextcloud/notify_push'

import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcHeaderMenu from '@nextcloud/vue/dist/Components/NcHeaderMenu.js'
import { createWebNotification } from './services/webNotificationsService.js'

export default {
	name: 'NotificationsApp',

	components: {
		IconBell,
		IconClose,
		IconMessage,
		IconNotification,
		NcButton,
		NcEmptyContent,
		NcHeaderMenu,
		Notification,
	},

	data() {
		return {
			webNotificationsGranted: false,
			backgroundFetching: false,
			hasNotifyPush: false,
			shutdown: false,
			hasThrottledPushNotifications: loadState('notifications', 'throttled_push_notifications'),
			notifications: [],
			lastETag: null,
			lastTabId: null,
			userStatus: null,
			tabId: null,

			/**
			 * Notifications older than this ID will not do a web notification.
			 *
			 * Sometimes a notification got "newly mounted" while being old.
			 * This can happen when a user has many notifications (100-1).
			 * The UI first only loads (100-76), if any notification is then
			 * resolved (e.g. by deleting or reading a chat), further old
			 * notifications (75+74) would be added to the UI and triggered
			 * a web notification (including call sound) in the past.
			 *
			 * This threshold ID is therefore updated to only higher values,
			 * before each pulling of notifications to ensure that we only ever
			 * web-notify on new notifications and not newly loaded old
			 * notifications.
			 */
			webNotificationsThresholdId: 0,

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

		// Set up the background checker
		this._setPollingInterval(this.pollIntervalBase)

		this._watchTabVisibility()
		subscribe('networkOffline', this.handleNetworkOffline)
		subscribe('networkOnline', this.handleNetworkOnline)
		subscribe('user_status:status.updated', this.userStatusUpdated)
	},

	beforeDestroy() {
		unsubscribe('user_status:status.updated', this.userStatusUpdated)
		unsubscribe('networkOffline', this.handleNetworkOffline)
		unsubscribe('networkOnline', this.handleNetworkOnline)
	},

	methods: {
		userStatusUpdated(state) {
			if (getCurrentUser().uid === state.userId) {
				this.userStatus = state.status
			}
		},

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
			if (this.notifications.length && this.notifications[0].notificationId > this.webNotificationsThresholdId) {
				this.webNotificationsThresholdId = this.notifications[0].notificationId
			}

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
				this.processWebNotifications(response.data)
				console.debug('Got notification data, restoring default polling interval.')
				this._setPollingInterval(this.pollIntervalBase)
				this._updateDocTitleOnNewNotifications(this.notifications)

				if (!this.backgroundFetching && this.notifications.length) {
					this.webNotificationsThresholdId = this.notifications[0].notificationId
				}
			} else if (response.status === 304) {
				// 304 - Not modified
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
			if (this.interval && pollInterval === this.pollIntervalCurrent) {
				return
			}

			console.debug('Polling interval updated to ' + pollInterval)

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

		processWebNotifications(notifications) {
			notifications.forEach(notification => {
				if (this.backgroundFetching) {
					// Can not rely on showBrowserNotifications because each tab should
					// be able to utilize the data from the notification in events.
					const event = { notification }
					emit('notifications:notification:received', event)
				}

				if (this.showBrowserNotifications && this.webNotificationsThresholdId < notification.notificationId) {
					createWebNotification(notification)
				}
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

.notification-wrapper {
	max-height: calc(100vh - 50px * 4);
	overflow: auto;
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
