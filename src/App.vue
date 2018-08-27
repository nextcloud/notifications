<template>
	<div v-if="!shutdown" class="notifications">
		<div class="notifications-button menutoggle" ref="button" :class="{ hasNotifications: notifications.length }"
			tabindex="0" role="button"
			aria-label="t('notifications', 'Notifications')"
			aria-haspopup="true" aria-controls="notification-container" aria-expanded="false">
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
	import axios from 'axios';

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

				if (this.notifications.length) {
					if (this.isRedThemed()) {
						iconPath += '-red';
					}
					iconPath += '-new';
				}

				if (this.invertedTheme()) {
					iconPath += '-dark';
				}

				return OC.imagePath('notifications', iconPath);
			}
		},

		methods: {
			onDismissAll: function() {
				axios
					.delete(OC.linkToOCS('apps/notifications/api/v2', 2) + 'notifications', { headers: { requesttoken: OC.requestToken } })
					.then(response => {
						this.notifications = [];
					})
					.catch(err => {
						OC.Notification.showTemporary(t('notifications', 'Failed to dismiss all notifications'));
					});
			},
			onRemove: function(index) {
				this.notifications.splice(index, 1);
			},

			invertedTheme: function() {
				return OCA.Theming && OCA.Theming.inverted;
			},

			isRedThemed: function() {
				if (OCA.Theming && OCA.Theming.color) {
					var hsl = this.rgbToHsl(OCA.Theming.color.substring(1, 3),
							OCA.Theming.color.substring(3, 5),
							OCA.Theming.color.substring(5, 7)),
						h = hsl[0] * 360;
					return (h >= 330 || h <= 15) && hsl[1] > 0.7 && (hsl[2] > 0.1 || hsl[2] < 0.6);
				}
				return false;
			},

			rgbToHsl: function (r, g, b) {
				r = parseInt(r, 16) / 255; g = parseInt(g, 16) / 255; b = parseInt(b, 16) / 255;
				var max = Math.max(r, g, b), min = Math.min(r, g, b);
				var h, s, l = (max + min) / 2;

				if (max === min) {
					h = s = 0;
				} else {
					var d = max - min;
					s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
					switch (max) {
						case r: h = (g - b) / d + (g < b ? 6 : 0); break;
						case g: h = (b - r) / d + 2; break;
						case b: h = (r - g) / d + 4; break;
					}
					h /= 6;
				}

				return [h, s, l];
			},

			/**
			 * Performs the AJAX request to retrieve the notifications
			 */
			_fetch: function() {
				axios
					.get(OC.linkToOCS('apps/notifications/api/v2', 2) + 'notifications', { headers: { requesttoken: OC.requestToken } })
					.then(response => {
						if (response.status === 204) {
							// 204 No Content - Intercept when no notifiers are there.
							this._shutDownNotifications();
						} else if (!_.isUndefined(response.data) && !_.isUndefined(response.data.ocs) && !_.isUndefined(response.data.ocs.data) && _.isArray(response.data.ocs.data)) {
							this.notifications = response.data.ocs.data;
						} else {
							console.debug("data.ocs.data is undefined or not an array");
						}
					})
					.catch(err => {
						if (!err.response) {
							console.debug('No response received, retrying');
							return;
						} else if (err.response.status === 503) {
							// 503 - Maintenance mode
							console.debug('Shutting down notifications: instance is in maintenance mode.');
						} else if (err.response.status === 404) {
							// 404 - App disabled
							console.debug('Shutting down notifications: app is disabled.');
						} else {
							console.error('Shutting down notifications: [' + err.response.status + '] ' + err.response.statusText);
						}

						this._shutDownNotifications();
					});
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
