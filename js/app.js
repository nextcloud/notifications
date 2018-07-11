/**
 * @copyright (c) 2016 Joas Schilling <coding@schilljs.com>
 * @copyright (c) 2015 Tom Needham <tom@owncloud.com>
 *
 * @author Tom Needham <tom@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

(function() {

	if (!OCA.Notifications) {
		OCA.Notifications = {};
	}

	OCA.Notifications = {
		/** @type {number} */
		pollInterval: 30000, // milliseconds
		/** @type {number|null} */
		interval: null,

		/** @type {OCA.Notifications.Notification[]|{}} */
		notifications: {},

		/** @type {Object} */
		$button: null,
		/** @type {Object} */
		$container: null,
		/** @type {Object} */
		$notifications: null,

		/** @type {Function} */
		notificationTemplate: null,

		/** @type {string} */
		_containerTemplate: '' +
		'<div class="notifications hidden">' +
		'  <div class="notifications-button menutoggle">' +
		'    <img class="svg" alt="" title="' + t('notifications', 'Notifications') + '"' +
		'      src="' + OC.imagePath('notifications', 'notifications') + '">' +
		'  </div>' +
		'  <div class="notification-container">' +
		'    <div class="notification-wrapper"></div>' +
		'    <div class="emptycontent">' +
		'      <h2>' + t('notifications', 'No notifications') + '</h2>' +
		'    </div>' +
		'  </div>' +
		'</div>',

		/** @type {string} */
		_notificationTemplate: '' +
		'<div class="notification" data-id="{{notification_id}}" data-timestamp="{{timestamp}}">' +
		'  <div class="notification-heading">' +
		'    <span class="notification-time has-tooltip live-relative-timestamp" data-timestamp="{{timestamp}}" title="{{absoluteDate}}">{{relativeDate}}</span>' +
		'    <div class="notification-delete">' +
		'      <span class="icon icon-close svg" title="' + t('notifications', 'Dismiss') + '"></span>' +
		'    </div>' +
		'  </div>' +
		'  {{#if link}}' +
		'    <a href="{{link}}" class="notification-subject full-subject-link">' +
		'      {{#if icon}}<span class="image"><img src="{{icon}}" class="notification-icon"></span>{{/if}}' +
		'      <span class="text">{{{subject}}}</span>' +
		'    </a>' +
		'  {{else}}' +
		'    <div class="notification-subject">' +
		'        {{#if icon}}<span class="image"><img src="{{icon}}" class="notification-icon"></span>{{/if}}' +
		'        <span class="text">{{{subject}}}</span>' +
		'    </div>' +
		'  {{/if}}' +
		'  {{#if message}}<div class="notification-message">{{{message}}}</div>{{/if}}' +
		'  <div class="notification-full-message hidden">{{{full_message}}}</div>' +
		'  {{#if actions}}<div class="notification-actions">' +
		'    {{#each actions}}' +
		'      <button class="action-button pull-right{{#if this.primary}} primary{{/if}}" data-type="{{this.type}}" ' +
		'data-href="{{this.link}}">{{this.label}}</button>' +
		'    {{/each}}' +
		'  </div>{{/if}}' +
		'</div>',

		/**
		 * Initialise the app
		 */
		initialise: function() {
			// Setup elements
			var compiledTemplate = Handlebars.compile(this._containerTemplate);
			this.notificationTemplate = Handlebars.compile(this._notificationTemplate);
			this.$notifications = $(compiledTemplate());
			this.$button = this.$notifications.find('.notifications-button');
			this.$container = this.$notifications.find('.notification-container');

			// Add to the UI
			$('form.searchbox').after(this.$notifications);

			// Initial call to the notification endpoint
			this.initialFetch();

			// Bind the button click event
			OC.registerMenu(this.$button, this.$container);
			this.$button.on('click', _.bind(this._onNotificationsButtonClick, this));

			this.$container.on('click', '.action-button', _.bind(this._onClickAction, this));
			this.$container.on('click', '.notification-delete', _.bind(this._onClickDismissNotification, this));

			// Setup the background checker
			if (oc_config.session_keepalive) {
				this.interval = setInterval(_.bind(this.backgroundFetch, this), this.pollInterval);
			}
		},

		/**
		 * Handles the notification dismiss click event
		 * @param {Event} event
		 */
		_onClickDismissNotification: function(event) {
			event.preventDefault();
			var self = this,
				$target = $(event.target),
				$notification = $target.closest('.notification'),
				id = $notification.attr('data-id');

			$notification.fadeOut(OC.menuSpeed);

			$.ajax({
				url: OC.linkToOCS('apps/notifications/api/v2', 2) + 'notifications/' + id,
				type: 'DELETE',
				beforeSend: function (request) {
					request.setRequestHeader('Accept', 'application/json');
				},
				success: function() {
					self._removeNotification(id);
				},
				error: function() {
					$notification.fadeIn(OC.menuSpeed);
					OC.Notification.showTemporary('Failed to perform action');
				}
			});
		},

		/**
		 * Handles the notification action click event
		 * @param {Event} event
		 */
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

		/**
		 * Remove a notification from the collection and the UI
		 * @param {Number} id
		 */
		_removeNotification: function(id) {
			var $notification = this.notifications[id].getElement();
			delete this.notifications[id];

			$notification.remove();
			if (this.numNotifications() === 0) {
				this._onHaveNoNotifications();
			}
		},

		/**
		 * Handles the notification button click event
		 */
		_onNotificationsButtonClick: function() {
			// Show a popup
			OC.showMenu(null, this.$container);
		},

		/**
		 * Initial fetch handler
		 */
		initialFetch: function() {
			var self = this;

			this._fetch(
				function(data) {
					// Fill Array
					_.each(data, function(notification) {
						var n = new self.Notification(notification);
						self.notifications[n.getId()] = n;
						self._addToUI(n);
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

			this._fetch(
				function(data) {
					var inJson = [],
						oldNum = self.numNotifications(),
						resort = false;

					_.each(data, function(notification) {
						var n = new self.Notification(notification);
						inJson.push(n.getId());
						if (!self.getNotification(n.getId())) {
							// New notification!
							self._onNewNotification(n);
							resort = true;
						}
					});

					if (resort) {
						self.$container.find('.notification').sort(function (prev, next) {
							return parseInt(next.dataset.timestamp) - parseInt(prev.dataset.timestamp);
						}).each(function() {
							$(self.$container.find('.notification-wrapper')).append(this);
						});
					}

					_.each(self.getNotifications(), function(n) {
						if (inJson.indexOf(n.getId()) === -1) {
							// Not in JSON, remove from UI
							self._onRemoveNotification(n);
						}
					});

					// Now check if we suddenly have notifs, or now none
					if (oldNum === 0 && self.numNotifications() !== 0) {
						// We now have some!
						self._onHaveNotifications();
					} else if (oldNum !== 0 && self.numNotifications() === 0) {
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
		},

		/**
		 * Handles removing the Notification from the UI when no longer in JSON
		 * @param {OCA.Notifications.Notification} notification
		 */
		_onRemoveNotification: function(notification) {
			notification.getElement().remove();
			delete this.notifications[notification.getId()];
		},

		/**
		 * Handle new notification received
		 * @param {OCA.Notifications.Notification} notification
		 */
		_onNewNotification: function(notification) {
			var self = this;
			// Add it to the array
			this.notifications[notification.getId()] = notification;
			// Add to the UI
			this._addToUI(notification);

			// Trigger browsers web notification
			// https://github.com/owncloud/notifications/issues/1
			if ("Notification" in window) {
				if (Notification.permission === "granted") {
					// If it's okay let's create a notification
					this._createWebNotification(notification);
				}

				// Otherwise, we need to ask the user for permission
				else if (Notification.permission !== 'denied') {
					Notification.requestPermission(function (permission) {
						// If the user accepts, let's create a notification
						if (permission === "granted") {
							self._createWebNotification(notification);
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
		_createWebNotification: function (notification) {
			var n = new Notification(notification.getPlainSubject(), {
				title: notification.getPlainSubject(),
				lang: OC.getLocale(),
				body: notification.getMessage(),
				icon: notification.getIcon(),
				tag: notification.getId()
			});

			if (notification.getLink()) {
				n.onclick = function(event) {
					event.preventDefault();
					window.location.href = notification.getLink();
				}
			}

			setTimeout(n.close.bind(n), 5000);
		},

		/**
		 * The app was disabled or has no notifiers, so we can stop polling
		 * And hide the UI as well
		 */
		_shutDownNotifications: function() {
			window.clearInterval(this.interval);
			this.$notifications.addClass('hidden');
		},

		/**
		 * Adds the notification to the UI
		 * @param {OCA.Notifications.Notification} notification
		 */
		_addToUI: function(notification) {
			var $element = $(notification.renderElement(this.notificationTemplate));

			$element.find('.avatar').each(function() {
				var element = $(this);
				if (element.data('user-display-name')) {
					element.avatar(element.data('user'), 21, undefined, false, undefined, element.data('user-display-name'));
				} else {
					element.avatar(element.data('user'), 21);
				}
			});

			$element.find('.avatar-name-wrapper').each(function() {
				var element = $(this);
				var avatar = element.find('.avatar');
				var label = element.find('strong');

				$.merge(avatar, label).contactsMenu(element.data('user'), 0, element);
			});

			$element.find('.has-tooltip').tooltip({
				container: this.$container.find('.notification-wrapper'),
				placement: 'bottom'
			});

			$element.find('.notification-message').on('click', function() {
				var $fullMessage = $(this).parent().find('.notification-full-message');
				$(this).addClass('hidden');
				$fullMessage.removeClass('hidden');
			});

			this.$container.find('.notification-wrapper').append($element);
		},

		/**
		 * Handle event when we have notifications (and didnt before)
		 */
		_onHaveNotifications: function() {
			// Add the button, title, etc
			var icon;
			if (OCA.Theming && OCA.Theming.inverted) {
				icon = 'notifications-new-dark';
			} else {
				icon = 'notifications-new';
			}
			this.$button.addClass('hasNotifications');
			this.$button.find('img').attr('src', OC.imagePath('notifications', icon))
				.animate({opacity: 0.6}, 600)
				.animate({opacity: 1}, 600)
				.animate({opacity: 0.6}, 600)
				.animate({opacity: 1}, 600);
			this.$container.find('.emptycontent').addClass('hidden');

			this.$notifications.removeClass('hidden');
		},

		/**
		 * Handle when all dismissed
		 */
		_onHaveNoNotifications: function() {
			// Remove the border
			this.$button.removeClass('hasNotifications');
			this.$container.find('.emptycontent').removeClass('hidden');
			this.$button.find('img').attr('src', OC.imagePath('notifications', 'notifications'));

			this.$notifications.addClass('hidden');
		},

		/**
		 * Performs the AJAX request to retrieve the notifications
		 * @param {Function} success
		 * @param {Function} failure
		 */
		_fetch: function(success, failure) {
			var self = this;
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
					self._shutDownNotifications();
				} else if (!_.isUndefined(data) && !_.isUndefined(data.ocs) && !_.isUndefined(data.ocs.data) && _.isArray(data.ocs.data)) {
					success(data.ocs.data, statusText, xhr);
				} else {
					console.debug("data.ocs.data is undefined or not an array");
				}
			});
			request.fail(failure);
		},

		/**
		 * Retrieves a notification object by id
		 * @param {int} id
		 * @return {OCA.Notifications.Notification|boolean}
		 */
		getNotification: function(id) {
			if (!_.isUndefined(this.notifications[id])) {
				return this.notifications[id];
			} else {
				return false;
			}
		},

		/**
		 * Returns all notification objects
		 * @return {OCA.Notifications.Notification[]}
		 */
		getNotifications: function() {
			return this.notifications;
		},

		/**
		 * Returns how many notifications in the UI
		 * @return {int}
		 */
		numNotifications: function() {
			return _.keys(this.notifications).length;
		}

	};
})();

$(document).ready(function () {
	OCA.Notifications.initialise();
});
