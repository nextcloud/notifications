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

        initialise: function() {
            // Go!

            // Setup elements
            var $notifications = $('<div class="notifications"></div>');
            this.$button = $('<div class="notifications-button menutoggle"><img class="svg" alt="Dismiss" src="' + OC.imagePath('notifications', 'notifications') + '"></div>');
            this.$container = $('<div class="notification-container"></div>');
            var $wrapper = $('<div class="notification-wrapper"></div>');

            // Empty content dropdown
            var $headLine = $('<h2></h2>');
            $headLine.text(t('notifications', 'No notifications'));
            var $emptyContent = $('<div class="emptycontent"></div>');
            $emptyContent.append($headLine);
            this.$container.append($emptyContent);

            $notifications.append(this.$button);
            $notifications.append(this.$container);
            this.$container.append($wrapper);

            // Add to the UI
            $('form.searchbox').before($notifications);

            // Inital call to the notification endpoint
            this.initialFetch();

            // Bind the button click event
            OC.registerMenu(this.$button, this.$container);
            this.$button.on('click', this._onNotificationsButtonClick);

			this.$container.on('click', '.action-button', _.bind(this._onClickAction, this));
			this.$container.on('click', '.notification-delete', _.bind(this._onClickDismissNotification, this));

            // Setup the background checker
            setInterval(this.backgroundFetch, this.pollInterval);
        },

		_onClickDismissNotification: function(event) {
			event.preventDefault();
			var $target = $(event.target);
			var $notification = $target.closest('.notification');
			var id = $notification.attr('data-id');

			$notification.fadeOut(OC.menuSpeed);

            $.ajax({
				url: OC.generateUrl('/apps/notifications/' + id),
                type: 'DELETE',
                success: function(data) {
					self._removeNotification(id);
                },
				error: function() {
					$notification.fadeIn(OC.menuSpeed);
                    OC.Notification.showTemporary('Failed to perform action');
                }
            });

			this._removeNotification($notification.attr('data-id'));
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
					self._removeNotification($notification.attr('data-id'));
                },
				error: function() {
					$notification.fadeIn(OC.menuSpeed);
                    OC.Notification.showTemporary('Failed to perform action');
                }
            });

		},

		_removeNotification: function(id) {
			var $notification = this.$container.find('.notification').filterAttr('id', id);
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
            this.fetch(
                function(data) {
                    // Fill Array
                    $.each(data, function(index) {
                        var n = new OCA.Notifications.Notif(data[index]);
                        OCA.Notifications.notifications[n.getId()] = n;
                        OCA.Notifications.addToUI(n);
                        OCA.Notifications.num++;
                    });
                    // Check if we have any, and notify the UI
                    if(OCA.Notifications.numNotifications() !== 0) {
                        OCA.Notifications._onHaveNotifications();
                    } else {
                        OCA.Notifications._onHaveNoNotifications();
                    }
                },
                function() {
                    OC.Notification.showTemporary('Failed to perform initial request for notifications');
                }
            );
        },

        /**
         * Background fetch handler
         */
        backgroundFetch: function() {
            OCA.Notifications.fetch(
                function(data) {
                    var inJson = [];
                    var oldNum = OCA.Notifications.numNotifications();
                    $.each(data, function(index) {
                        var n = new OCA.Notifications.Notif(data[index]);
                        inJson.push(n.getId());
                        if(!OCA.Notifications.getNotification(n.getId())){
                            // New notification!
                            OCA.Notifications._onNewNotification(n);
                        }
                    });
                    // TODO check if any removed from JSON
                    for(var n in OCA.Notifications.getNotifications()) {
                        if(inJson.indexOf(OCA.Notifications.getNotifications()[n].getId()) === -1) {
                            // Not in JSON, remove from UI
                            OCA.Notifications._onRemoveNotification(OCA.Notifications.getNotifications()[n]);
                        }
                    }

                    // Now check if we suddenly have notifs, or now none
                    if(oldNum == 0 && OCA.Notifications.numNotifications() !== 0) {
                        // We now have some!
                        OCA.Notifications._onHaveNotifications();
                    } else if(oldNum != 0 && OCA.Notifications.numNotifications() === 0) {
                        // Now we have none
                        OCA.Notifications._onHaveNoNotifications();
                    }
                },
                function() {
                    // Bad
                    console.log('Failed to fetch notifications');
                }
            );
        },

        /**
         * Handles removing the Notification from the UI when no longer in JSON
         * @param {OCA.Notifications.Notification} notification
         */
        _onRemoveNotification: function(notification) {
            $('div.notification[data-id='+escapeHTML(notification.getId())+']').remove();
            delete OCA.Notifications.notifications[notification.getId()];
            OCA.Notifications.num--;
        },

        /**
         * Handle new notification received
         * @param {OCA.Notifications.Notification} notification
         */
        _onNewNotification: function(notification) {
            OCA.Notifications.num++;
            // Add it to the array
            OCA.Notifications.notifications[notification.getId()] = notification;
            // Add to the UI
            OCA.Notifications.addToUI(notification);
            // TODO make a noise? Anything else?
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
            $('div.notifications-button')
            .addClass('hasNotifications')
            .animate({opacity: 0.5})
            .animate({opacity: 1})
            .animate({opacity: 0.5})
            .animate({opacity: 1})
            .animate({opacity: 0.7});
            $('div.notifications .emptycontent').addClass('hidden');
            this.$button.find('img').attr('src', OC.imagePath('notifications', 'notifications-new'));
        },

        /**
         * Handle when all dismissed
         */
        _onHaveNoNotifications: function() {
            // Remove the border
            $('div.notifications-button').removeClass('hasNotifications');
            $('div.notifications .emptycontent').removeClass('hidden');
            this.$button.find('img').attr('src', OC.imagePath('notifications', 'notifications'));
        },

        /**
         * Performs the AJAX request to retrieve the notifications
         * @param {Function} success
         * @param {Function} failure
         */
        fetch: function(success, failure){
            var request = $.ajax({
                url: OC.generateUrl('/apps/notifications'),
                type: 'GET'
            });
            request.success(success);
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
            return OCA.Notifications.num;
        }

    };
})();

$(document).ready(function () {
    OCA.Notifications.initialise();
});
