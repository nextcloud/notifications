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
	import axios from 'axios';

	export default {
		name: 'app',

		data: function () {
			return {
				hadNotifications: false,
				backgroundFetching: false,
				shutdown: false,
				playedSoundVideoCall: false,
				playedSoundOther: false,
				notifications: [],

				/** @type {number} */
				pollInterval: 30000, // milliseconds

				/** @type {number|null} */
				interval: null
			};
		},

		_$icon: null,
		_$audioVideoCall: null,
		_$audioOther: null,

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
						if (err.response.status === 503) {
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
				this.playedSoundVideoCall = false;
				this.playedSoundOther = false;
				this._fetch();
			},

			/**
			 * The app was disabled or has no notifiers, so we can stop polling
			 * And hide the UI as well
			 */
			_shutDownNotifications: function() {
				window.clearInterval(this.interval);
				this.shutdown = true;
			},

			playSoundVideoCall: function() {
				if (this.playedSoundVideoCall) {
					// Already played in this background fetch
					return;
				}

				this._$audioVideoCall.play();
				this.playedSoundVideoCall = true;
			},

			playSoundOther: function() {
				if (this.playedSoundVideoCall || this.playedSoundOther) {
					// Already played in this background fetch
					return;
				}

				this._$audioOther.play();
				this.playedSoundOther = true;
			}
		},

		components: {
			notification
		},

		mounted: function () {
			this._$icon = $(this.$refs.icon);

			var $audio = $('<audio>');
			$('<source>').attr('src', OC.linkTo('notifications', 'resources/videocall.mp3')).appendTo($audio);
			$('<source>').attr('src', OC.linkTo('notifications', 'resources/videocall.ogg')).appendTo($audio);
			this._$audioVideoCall = $audio[0];

			$audio = $('<audio>');
			$('<source>').attr('src', OC.linkTo('notifications', 'resources/notification.mp3')).appendTo($audio);
			$('<source>').attr('src', OC.linkTo('notifications', 'resources/notification.ogg')).appendTo($audio);
			this._$audioOther = $audio[0];

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
