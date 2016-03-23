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
		this.timestamp = moment(jsonData.datetime).format('X');
		this.object_type = jsonData.object_type;
		this.object_id = jsonData.object_id;
		this.subject = jsonData.subject;
		this.message = jsonData.message;
		this.link = jsonData.link;
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

		actions: [],

		notification_id: null,

		getSubject: function() {
			return this.subject;
		},

		getTimestamp: function() {
			return this.timestamp;
		},

		getObjectId: function() {
			return this.object_id;
		},

		getLink: function() {
			return this.link;
		},

		getActions: function() {
			return this.actions;
		},

		getId: function() {
			return this.notification_id;
		},

		getMessage: function() {
			var message = this.message;

			/**
			 * Trim on word end after 100 chars or hard 120 chars
			 */
			if (message.length > 120) {
				var spacePosition = message.indexOf(' ', 100);
				if (spacePosition !== -1 && spacePosition <= 120) {
					message = message.substring(0, spacePosition);
				} else {
					message = message.substring(0, 120);
				}
				message += t('notifications', '…');
			}

			message = escapeHTML(message);
			message = message.replace(new RegExp("\n", 'g'), ' ');

			return message;
		},

		getEl: function() {
			return $('div.notification[data-id='+escapeHTML(this.getId())+']');
		},

		getApp: function() {
			return this.app;
		},

		/**
		 * Generates the HTML for the notification
		 */
		renderElement: function() {
			// FIXME: use handlebars template
			var el = $('<div class="notification"></div>');
			el.attr('data-id', escapeHTML(this.getId()));
			el.attr('data-timestamp', escapeHTML(this.getTimestamp()));

			if (this.getLink()) {
				el.append('<a href="'+this.getLink()+'" class="notification-subject"> '+escapeHTML(this.getSubject())+'</a>');
			} else {
				el.append('<div class="notification-subject"> '+escapeHTML(this.getSubject())+'</div>');
			}
			el.append('<div class="notification-message">'+this.getMessage()+'</div>');
			// Add actions
			var actions = $('<div class="notification-actions"></div>');
			var actionsData = this.getActions();
			_.each(actionsData, function(actionData) {
				// FIXME: use handlebars template
				actions.append(
					'<button class="action-button' + (actionData.primary ? ' primary': '') + '" data-type="' + escapeHTML(actionData.type) + '" ' +
					'data-href="'+escapeHTML(actionData.link)+'">'+escapeHTML(actionData.label)+'</button>'
				);
				// TODO create event handler on click for given action type
			});
			el.append(actions);
			el.append('<div style="display: none;" class="notification-delete"><img class="svg" alt="' + t('notifications', 'Dismiss') + '" src="' + OC.imagePath('core', 'actions/close') + '"></div>');
			return el;
		},

		/**
		 * Register notification Binds
		 */
		bindNotificationEvents: function() {

		}

	};

	OCA.Notifications.Notif = Notif;

})();
