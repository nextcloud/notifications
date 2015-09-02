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

    var App = {

        notifications: [],

        open: false,

        initialise: function() {
            // Go!

            // Setup elements
            var $notifications = $('<div class="notifications"></div>');
            var $button = $('<div class="notifications-button svg"></div>');
            var $container = $('<div class="notification-container"></div>');
            var $wrapper = $('<div class="notification-wrapper"></div>');
            $notifications.append($button);
            $notifications.append($container);
            $container.append($wrapper);

            // Add to the UI
            $('form.searchbox').before($notifications);

            // Setup the background timer for polling the server

            // Inital call to the notification endpoint
            this.initialFetch();

            // Bind the button click event
            $button.on('click', this._onNotificationsButtonClick);
        },

        /**
         * Handles the notification button click event
         */
        _onNotificationsButtonClick: function(e) {
            // Show a popup
            $('div.notification-container').slideToggle(200);
        },

        initialFetch: function() {
            this.fetch(
                function(data) {
                    // Fill Array
                    $.each(data, function(index) {
                        var n = new OCA.Notifications.Notif(data[index]);
                        OCA.Notifications.notifications.push(n);
                        $('div.notification-wrapper').prepend(n.renderElement());
                    });
                    // Check if we have any, and notify the UI
                    if(OCA.Notifications.notifications.length) {
                        OCA.Notifications._onHaveNotifications;
                    }
                },
                function(jqXHR) {
                    console.log('Failed to perform initial request for notifications');
                }
            );
        },

        /**
         * Background fetch handler
         */
        backgroundFetch: function() {
            this.fetch(
                function(data) {
                    $.each(data, function(index) {
                        var n = new OCA.Notifications.Notif(data[index]);
                        if(!OCA.Notifications.getNotification(n.getId())){
                            // New notification!
                            OCA.Notifications._onNewNotification(n);
                        }
                    });
                    // TODO check if any removed from JSON
                },
                function(jqXHR) {
                    // Bad
                    console.log('Failed to fetch notifications');
                }
            )
        },

        /**
         * Handle event when we have notifications (and didnt before)
         */
        _onHaveNotifications: function() {
            // Add the button, title, etc
            alert('woop we have notifications');
        },

        /**
         * Performs the AJAX request to retrieve the notifications
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
         */
        getNotification: function(id) {
            if(this.notifications[id] != undefined) {
                return this.notifications[id];
            } else {
                return false;
            }
        },

        /**
         * Handles the returned data from the AJAX call
         */
        parseNotifications: function(responseData) {

        },

        /**
         * Handle new notification received
         * @param OCA.Notifications.Notification
         */
        _onNewNotification: function(notification) {
            // Add it to the array
            OCA.Notifications.notifications.push(notification);
            // Update the notification numbers
            $('notification-button').text(OCA.Notifications.notifications.length);
        }
    }

    OCA.Notifications = App;

})();

$(document).ready(function () {
    OCA.Notifications.initialise();
});
