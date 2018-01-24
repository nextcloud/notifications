<template>
	<div v-if="!shutdown" class="notifications">
		<div class="notifications-button menutoggle" :class="{ hasNotifications: notifications.length }">
			<img class="svg" alt="" :title="t_notifications" :src="iconPath">
		</div>
		<div class="notification-container">
			<div class="notification-wrapper" v-if="notifications.length">
				<notification v-for="(n, index) in notifications" v-bind="n" :key="n.notification_id" @remove="onRemove" ></notification>
			</div>
			<div class="emptycontent" v-else>
				<h2>{{t_empty}}</h2>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "root",

		el: '#notifications',

		data: function () {
			return {
				hadNotifications: false,
				backgroundFetching: false,
				shutdown: false,
				notifications: []
			};
		},

		_$el: null,
		_$button: null,
		_$icon: null,
		_$container: null,

		computed: {
			t_empty: function() {
				return t('notifications', 'No notifications');
			},
			t_notifications: function() {
				return t('notifications', 'Notifications');
			},
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
			'notification': require('./notification.vue')
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
	}
</script>
