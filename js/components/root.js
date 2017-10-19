/**
 * @copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

(function(OC, OCA, $) {
	OCA.Notifications = OCA.Notifications || {};
	OCA.Notifications.Components = OCA.Notifications.Components || {};

	OCA.Notifications.Components.Root = {
		template: '' +
		'<div v-if="!shutdown" class="notifications">' +
		'  <div class="notifications-button menutoggle" :class="{ hasNotifications: notifications.length }">' +
		'    <img class="svg" alt="" title="' + t('notifications', 'Notifications') + '" :src="iconPath">' +
		'  </div>' +
		'  <div class="notification-container">' +
		'    <div class="notification-wrapper" v-if="notifications.length">' +
		'      <notification v-for="(n, index) in notifications" v-bind="n" :key="n.notification_id" @remove="onRemove"></notification>' +
		'    </div>' +
		'    <div class="emptycontent" v-else>' +
		'      <h2>' + t('notifications', 'No notifications') + '</h2>' +
		'    </div>' +
		'  </div>' +
		'</div>',

		el: '#notifications',
		data: {
			hadNotifications: false,
			backgroundFetching: false,
			shutdown: false,
			notifications: []
		},

		_$el: null,
		_$button: null,
		_$icon: null,
		_$container: null,

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
			onRemove: function(index) {
				this.notifications.splice(index, 1);
			}
		},

		components: {
			'notification': OCA.Notifications.Components.Notification
		},

		mounted: function () {
			this._$el = $(this.$el);
			this._$button = this._$el.find('.notifications-button');
			this._$icon = this._$button.find('img');
			this._$container = this._$el.find('.notification-container');

			// Bind the button click event
			OC.registerMenu(this._$button, this._$container);
		},

		updated: function() {
			this._$button.find('img').attr('src', this.iconPath);

			if (!this.hadNotifications && this.notifications.length) {
				this._$icon
					.animate({opacity: 0.6}, 600)
					.animate({opacity: 1}, 600)
					.animate({opacity: 0.6}, 600)
					.animate({opacity: 1}, 600);
			}

			this.hadNotifications = this.notifications.length > 0;
		}
	};
})(OC, OCA, $);
