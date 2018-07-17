<template>
	<div v-if="!shutdown" class="notifications">
		<div class="notifications-button menutoggle" ref="button" :class="{ hasNotifications: notifications.length }">
			<img class="svg" alt="" ref="icon" :title="t('notifications', 'Notifications')" :src="iconPath">
		</div>
		<div class="notification-container"  ref="container">
			<div class="notification-wrapper" v-if="notifications.length">
				<notification v-for="n in notifications" v-bind="n" :key="n.notification_id" @remove="onRemove" ></notification>
				<div class="dismiss-all" v-if="notifications.length > 2" @click="onDismissAll">
					<span class="icon icon-close svg" :title="t('notifications', 'Dismiss all notifications')"></span> {{ t('notifications', 'Dismiss all notifications') }}
				</div>
			</div>
			<div class="emptycontent" v-else>
				<h2>{{ t('notifications', 'No notifications') }}</h2>
			</div>
		</div>
	</div>
</template>

<script>
	import notification from './components/notification';
	// import axios from 'axios';

	export default {
		name: 'app',

		data: function () {
			return {
				hadNotifications: false,
				backgroundFetching: false,
				shutdown: false,
				notifications: [],

				/** @type {number} */
				pollInterval: 30000, // milliseconds

				/** @type {number|null} */
				interval: null,
			};
		},

		_$icon: null,

		computed: {
			iconPath: function() {
				var iconPath = 'notifications';

				if (/*this.backgroundFetching &&*/ this.notifications.length) {
					iconPath += '-new';
				}

				if (this.invertedTheme) {
					iconPath += '-dark';
				}

				return OC.imagePath('notifications', iconPath);
			},
			invertedTheme: function() {
				return OCA.Theming && OCA.Theming.inverted;
			}
		},

		methods: {
			onDismissAll: function() {
				$.ajax({
					url: OC.linkToOCS('apps/notifications/api/v2', 2) + 'notifications',
					type: 'DELETE',
					success: function () {
						this.notifications = [];
					}.bind(this),
					error: function () {
						OC.Notification.showTemporary(t('notifications', 'Failed to dismiss all notifications'));
					}
				});
			},
			onRemove: function(index) {
				this.notifications.splice(index, 1);
			},

			/**
			 * Performs the AJAX request to retrieve the notifications
			 */
			_fetch: function() {
				var request = $.ajax({
					url: OC.linkToOCS('apps/notifications/api/v2', 2) + 'notifications',
					type: 'GET',
					beforeSend: function (request) {
						request.setRequestHeader('Accept', 'application/json');
					}
				});

				request.done(function(data, statusText, xhr) {
					if (xhr.status === 204) {
						// 204 No Content - Intercept when no notifiers are there.
						this._shutDownNotifications();
					} else if (!_.isUndefined(data) && !_.isUndefined(data.ocs) && !_.isUndefined(data.ocs.data) && _.isArray(data.ocs.data)) {
						this.notifications = data.ocs.data;
					} else {
						console.debug("data.ocs.data is undefined or not an array");
					}
				}.bind(this));
				request.fail(function(xhr) {
					if (xhr.status === 503) {
						// 503 - Maintenance mode
						console.debug('Shutting down notifications: instance is in maintenance mode.');
					} else if (xhr.status === 404) {
						// 404 - App disabled
						console.debug('Shutting down notifications: app is disabled.');
					} else {
						console.error('Shutting down notifications: [' + xhr.status + '] ' + xhr.statusText);
					}

					this._shutDownNotifications();
				}.bind(this));
			},

			_backgroundFetch: function() {
				this.backgroundFetching = true;
				this._fetch();
			},

			/**
			 * The app was disabled or has no notifiers, so we can stop polling
			 * And hide the UI as well
			 */
			_shutDownNotifications: function() {
				window.clearInterval(this.interval);
				this.shutdown = true;
			}
		},

		components: {
			notification
		},

		mounted: function () {
			this._$icon = $(this.$refs.icon);

			// Bind the button click event
			OC.registerMenu($(this.$refs.button), $(this.$refs.container), undefined, true);

			// Initial call to the notification endpoint
			this._fetch();

			// Setup the background checker
			if (oc_config.session_keepalive) {
				this.interval = setInterval(this._backgroundFetch.bind(this), this.pollInterval);
			}
		},

		updated: function() {
			this._$icon.attr('src', this.iconPath);

			if (!this.hadNotifications && this.notifications.length) {
				this._$icon
					.animate({opacity: 0.6}, 600)
					.animate({opacity: 1}, 600)
					.animate({opacity: 0.6}, 600)
					.animate({opacity: 1}, 600);
			}

			this.hadNotifications = this.notifications.length > 0;
		}
	}
</script>
