<template>
	<div v-if="!shutdown" class="notifications">
		<div class="notifications-button menutoggle" :class="{ hasNotifications: notifications.length }">
			<img class="svg" alt="" :title="t('notifications', 'Notifications')" :src="iconPath">
		</div>
		<div class="notification-container">
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
