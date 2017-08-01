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

	/**
	 * Initialise the notification
	 *
	 * @param {Object} data
	 * @param {int} data.notification_id
	 * @param {string} data.app
	 * @param {string} data.user
	 * @param {string} data.datetime
	 * @param {string} data.object_type
	 * @param {string} data.object_id
	 * @param {string} data.subject
	 * @param {string} data.subjectRich
	 * @param {Object[]} data.subjectRichParameters
	 * @param {string} data.message
	 * @param {string} data.link
	 * @param {string} data.icon
	 * @param {Object[]} data.actions
	 */
	OCA.Notifications.Notification = function(data) {
		this.data = data;
	};

	OCA.Notifications.Notification.prototype = {
		getId: function() {
			return this.data.notification_id;
		},

		getApp: function() {
			return this.data.app;
		},

		getUser: function() {
			return this.data.user;
		},

		getTimestamp: function() {
			if (_.isUndefined(this.data.timestamp)) {
				this.data.timestamp = moment(this.data.datetime).format('X') * 1000;
			}

			return this.data.timestamp;
		},

		getObjectType: function() {
			return this.data.object_type;
		},

		getObjectId: function() {
			return this.data.object_id;
		},

		getSubject: function() {
			if (this.data.subjectRich.length !== 0) {
				return OCA.Notifications.RichObjectStringParser.parseMessage(
					this.data.subjectRich,
					this.data.subjectRichParameters
				);
			}

			return this.getPlainSubject();
		},

		getPlainSubject: function() {
			return this.data.subject;
		},

		getMessage: function() {
			var message = this.data.message;

			/**
			 * Trim on word end after 180 chars or hard 200 chars
			 */
			if (message.length > 200) {
				var spacePosition = message.indexOf(' ', 180);
				if (spacePosition !== -1 && spacePosition <= 200) {
					message = message.substring(0, spacePosition);
				} else {
					message = message.substring(0, 200);
				}
				message += '…';
			}

			return message.replace(new RegExp("\n", 'g'), ' ');
		},

		getLink: function() {
			if (this.getSubject().indexOf('<a ') === -1) {
				return this.data.link;
			}
			return '';
		},

		getIcon: function() {
			return this.data.icon;
		},

		getActions: function() {
			return this.data.actions;
		},

		getElement: function() {
			return $('div.notification[data-id=' + parseInt(this.getId(), 10) + ']');
		},

		/**
		 * Generates the HTML for the notification
		 * @param {Function} template
		 */
		renderElement: function(template) {
			var temp = _.extend({}, this.data);
			return template(_.extend(temp, {
				subject: this.getSubject(),
				link: this.getLink(),
				message: this.getMessage(),
				full_message: this.data.message,
				timestamp: this.getTimestamp(),
				relativeDate: OC.Util.relativeModifiedDate(this.getTimestamp()),
				absoluteDate: OC.Util.formatDate(this.getTimestamp())
			}));
		}
	};

})();
