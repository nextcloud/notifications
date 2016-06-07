/**
 * ownCloud - Notifications
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Tom Needham <tom@owncloud.com>
 * @copyright Tom Needham 2015
 */

(function() {

	if (!OCA.Notifications) {
		OCA.Notifications = {};
	}

	OCA.Notifications = {

		notifications: {},

		num: 0,

		pollInterval: 30000, // milliseconds

		open: false,

		$button: null,

		$container: null,

		$notifications: null,

		interval: null,

		initialise: function() {
			// Go!

			// Setup elements
			this.$notifications = $('<div class="notifications hidden"></div>');
			this.$button = $('<div class="notifications-button menutoggle"><img class="svg" alt="' + t('notifications', 'Notifications') + '" src="' + OC.imagePath('notifications', 'notifications') + '"></div>');
			this.$container = $('<div class="notification-container"></div>');
			var $wrapper = $('<div class="notification-wrapper"></div>');

			// Empty content dropdown
			var $headLine = $('<h2></h2>');
			$headLine.text(t('notifications', 'No notifications'));
			var $emptyContent = $('<div class="emptycontent"></div>');
			$emptyContent.append($headLine);
			this.$container.append($emptyContent);

			this.$notifications.append(this.$button);
			this.$notifications.append(this.$container);
			this.$container.append($wrapper);

			// Add to the UI
			$('form.searchbox').before(this.$notifications);

			// Initial call to the notification endpoint
			this.initialFetch();

			// Bind the button click event
			OC.registerMenu(this.$button, this.$container);
			this.$button.on('click', this._onNotificationsButtonClick);

			this.$container.on('click', '.action-button', _.bind(this._onClickAction, this));
			this.$container.on('click', '.notification-delete', _.bind(this._onClickDismissNotification, this));

			// Setup the background checker
			this.interval = setInterval(_.bind(this.backgroundFetch, this), this.pollInterval);
		},

		_onClickDismissNotification: function(event) {
			event.preventDefault();
			var self = this,
				$target = $(event.target),
				$notification = $target.closest('.notification'),
				id = $notification.attr('data-id');

			$notification.fadeOut(OC.menuSpeed);

			$.ajax({
				url: OC.linkToOCS('apps/notifications/api/v1', 2) + 'notifications/' + id + '?format=json',
				type: 'DELETE',
				success: function(data) {
					self._removeNotification(id);
				},
				error: function() {
					$notification.fadeIn(OC.menuSpeed);
					OC.Notification.showTemporary('Failed to perform action');
				}
			});
		},

		_onClickAction: function(event) {
			event.preventDefault();
			var self = this;
			var $target = $(event.target);
			var $notification = $target.closest('.notification');
			var actionType = $target.attr('data-type') || 'GET';
			var actionUrl = $target.attr('data-href');

			$notification.fadeOut(OC.menuSpeed);

			$.ajax({
				url: actionUrl,
				type: actionType,
				success: function(data) {
					$('body').trigger(new $.Event('OCA.Notification.Action', {
						notification: self.notifications[$notification.attr('data-id')],
						action: {
							url: actionUrl,
							type: actionType
						}
					}));
					self._removeNotification($notification.attr('data-id'));
				},
				error: function() {
					$notification.fadeIn(OC.menuSpeed);
					OC.Notification.showTemporary('Failed to perform action');
				}
			});

		},

		_removeNotification: function(id) {
			var $notification = this.$container.find('.notification[data-id=' + id + ']');
			delete OCA.Notifications.notifications[id];

			$notification.remove();
			if (_.keys(OCA.Notifications.notifications).length === 0) {
				this._onHaveNoNotifications();
			}
		},

		/**
		 * Handles the notification button click event
		 */
		_onNotificationsButtonClick: function() {
			// Show a popup
			OC.showMenu(null, OCA.Notifications.$container);
		},

		initialFetch: function() {
			var self = this;

			this.fetch(
				function(data) {
					// Fill Array
					$.each(data, function(index) {
						var n = new self.Notif(data[index]);
						self.notifications[n.getId()] = n;
						self.addToUI(n);
					});
					// Check if we have any, and notify the UI
					if (self.numNotifications() !== 0) {
						self._onHaveNotifications();
					} else {
						self._onHaveNoNotifications();
					}
				},
				_.bind(self._onFetchError, self)
			);
		},

		/**
		 * Background fetch handler
		 */
		backgroundFetch: function() {
			var self = this;

			this.fetch(
				function(data) {
					var inJson = [];
					var oldNum = self.numNotifications();
					$.each(data, function(index) {
						var n = new self.Notif(data[index]);
						inJson.push(n.getId());
						if (!self.getNotification(n.getId())){
							// New notification!
							self._onNewNotification(n);
						}
					});

					for (var n in self.getNotifications()) {
						if (inJson.indexOf(self.getNotifications()[n].getId()) === -1) {
							// Not in JSON, remove from UI
							self._onRemoveNotification(self.getNotifications()[n]);
						}
					}

					// Now check if we suddenly have notifs, or now none
					if (oldNum == 0 && self.numNotifications() !== 0) {
						// We now have some!
						self._onHaveNotifications();
					} else if (oldNum != 0 && self.numNotifications() === 0) {
						// Now we have none
						self._onHaveNoNotifications();
					}
				},
				_.bind(self._onFetchError, self)
			);
		},

		/**
		 * Handles errors when requesting the notifications
		 * @param {XMLHttpRequest} xhr
		 */
		_onFetchError: function(xhr) {
			if (xhr.status === 404) {
				// 404 Not Found - stop polling
				this._shutDownNotifications();
			} else {
				OC.Notification.showTemporary('Failed to request notifications. Please try to refresh the page manually.');
			}
		},

		/**
		 * Handles removing the Notification from the UI when no longer in JSON
		 * @param {OCA.Notifications.Notification} notification
		 */
		_onRemoveNotification: function(notification) {
			$('div.notification[data-id='+escapeHTML(notification.getId())+']').remove();
			delete OCA.Notifications.notifications[notification.getId()];
		},

		/**
		 * Handle new notification received
		 * @param {OCA.Notifications.Notification} notification
		 */
		_onNewNotification: function(notification) {
			// Add it to the array
			OCA.Notifications.notifications[notification.getId()] = notification;
			// Add to the UI
			OCA.Notifications.addToUI(notification);

			// Trigger browsers web notification
			// https://github.com/owncloud/notifications/issues/1
			if ("Notification" in window) {
				if (Notification.permission === "granted") {
					// If it's okay let's create a notification
					OCA.Notifications.createWebNotification(notification);
				}

				// Otherwise, we need to ask the user for permission
				else if (Notification.permission !== 'denied') {
					Notification.requestPermission(function (permission) {
						// If the user accepts, let's create a notification
						if (permission === "granted") {
							OCA.Notifications.createWebNotification(notification);
						}
					});
				}
			}
		},

		/**
		 * Create a browser notification
		 *
		 * @see https://developer.mozilla.org/en/docs/Web/API/notification
		 * @param {OCA.Notifications.Notification} notification
		 */
		createWebNotification: function (notification) {
			var n = new Notification(notification.getSubject(), {
				title: notification.getSubject(),
				lang: OC.getLocale(),
				body: notification.getMessage(),
				tag: notification.getId()
			});
			setTimeout(n.close.bind(n), 5000);
		},

		_shutDownNotifications: function() {
			// The app was disabled or has no notifiers, so we can stop polling
			// And hide the UI as well
			window.clearInterval(this.interval);
			this.$notifications.addClass('hidden');
		},

		/**
		 * Adds the notification to the UI
		 * @param {OCA.Notifications.Notification} notification
		 */
		addToUI: function(notification) {
			$('div.notification-wrapper').prepend(notification.renderElement());
		},

		/**
		 * Handle event when we have notifications (and didnt before)
		 */
		_onHaveNotifications: function() {
			// Add the button, title, etc
			this.$button.addClass('hasNotifications');
			this.$button.find('img').attr('src', OC.imagePath('notifications', 'notifications-new'))
				.animate({opacity: 0.5}, 600)
				.animate({opacity: 1}, 600)
				.animate({opacity: 0.5}, 600)
				.animate({opacity: 1}, 600)
				.animate({opacity: 0.7}, 600);
			this.$container.find('.emptycontent').addClass('hidden');

			this.$notifications.removeClass('hidden');
		},

		/**
		 * Handle when all dismissed
		 */
		_onHaveNoNotifications: function() {
			// Remove the border
			$('div.notifications-button').removeClass('hasNotifications');
			$('div.notifications .emptycontent').removeClass('hidden');
			this.$button.find('img').attr('src', OC.imagePath('notifications', 'notifications'));

			this.$notifications.addClass('hidden');
		},

		/**
		 * Performs the AJAX request to retrieve the notifications
		 * @param {Function} success
		 * @param {Function} failure
		 */
		fetch: function(success, failure){
			var self = this;
			var request = $.ajax({
				url: OC.linkToOCS('apps/notifications/api/v1', 2) + 'notifications?format=json',
				type: 'GET'
			});


			request.done(function(data, statusText, xhr) {
				if (xhr.status === 204 || data.ocs.meta.statuscode === 204) {
					// 204 No Content - Intercept when no notifiers are there.
					self._shutDownNotifications();
				} else {
					success(data.ocs.data, statusText, xhr);
				}
			});
			request.fail(failure);
		},

		/**
		 * Retrieves a notification object by id
		 * @param {int} id
		 */
		getNotification: function(id) {
			if(OCA.Notifications.notifications[id] != undefined) {
				return OCA.Notifications.notifications[id];
			} else {
				return false;
			}
		},

		/**
		 * Returns all notification objects
		 */
		getNotifications: function() {
			return this.notifications;
		},

		/**
		 * Handles the returned data from the AJAX call
		 * @param {object} responseData
		 */
		parseNotifications: function(responseData) {

		},

		/**
		 * Returns how many notifications in the UI
		 */
		numNotifications: function() {
			return _.keys(this.notifications).length;
		}

	};
})();

$(document).ready(function () {
	OCA.Notifications.initialise();
});
