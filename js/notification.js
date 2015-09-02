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

     /**
      * Initialise the notification
      */
 	var Notif = function(jsonData){
        // TODO handle defaults
        this.app = jsonData.app;
        this.user = jsonData.user;
        this.timestamp = jsonData.timestamp;
        this.object_type = jsonData.object_type;
        this.object_id = jsonData.object_id;
        this.subject = jsonData.subject;
        this.message = jsonData.message;
        this.link = jsonData.link;
        this.icon = jsonData.icon;
        this.actions = jsonData.actions; // TODO some parsing here?
        this.notification_id = jsonData.notification_id;
 	};

 	Notif.prototype = {

        app: null,

        user: null,

        timestamp: null,

        object_type: null,

        object_id: null,

        subject: null,

        message: null,

        link: null,

        icon: null,

        actions: [],

        notification_id: null,

        getActions: function() {
            return this.actions;
        },

        getSubject: function() {
            return this.subject;
        },

        getTimestamp: function() {
            return this.timestamp;
        },

        getObjectId: function() {
            return this.object_id;
        },

        getActions: function() {
            return this.actions;
        },

        getId: function() {
            return this.notification_id;
        },

        getMessage: function() {
            return this.message;
        },

        getEl: function() {
            return $('div.notification[data-id='+this.getId()+']');
        },

        /**
         * Generates the HTML for the notification
         */
        renderElement: function() {
            var el = $('<div class="notification"></div>');
            el.attr('data-id', this.getId());
            el.attr('data-timestamp', this.getTimestamp());
            el.append('<div class="notification-subject">'+this.getSubject()+'</div>');
            el.append('<div class="notification-message">'+this.getMessage()+'</div>');
            // Add actions
            var actions = $('<div class="actions"></div>');
            var actionsData = this.getActions();
            $.each(actionsData, function(index) {
                actions.append('<a class="button" href="'+actionsData[index].link+'">'+actionsData[index].label+'</a>');
                // TODO create event handler on click for given action type
            });
            el.append(actions);
            el.append('<div style="display: none;" class="notification-delete"><img class="svg" alt="Dismiss" src="/core/img/actions/close.svg"></div>');
            return el;
        },

        /**
         * Register notification Binds
         */
        bindNotificationEvents: function() {

        }

    }

    OCA.Notifications.Notif = Notif;

})();
