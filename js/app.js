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

(function(OC, OCA, $, _) {
	OCA.Notifications = OCA.Notifications || {};

	OCA.Notifications.App = {

		/** @type {number} */
		pollInterval: 30000, // milliseconds

		/** @type {number|null} */
		interval: null,

		/** @type {Vue|null} */
		vm: null,

		/**
		 * Initialise the app
		 */
		initialise: function() {

			// Add to the UI
			$('form.searchbox').after($('<div>').attr('id', 'notifications'));

			// Setup Vue
			this.vm = new Vue(OCA.Notifications.Components.Root);

			// Initial call to the notification endpoint
			this._fetch();

			// Setup the background checker
			this.interval = setInterval(this._backgroundFetch.bind(this), this.pollInterval);
		},

		/**
		 * Performs the AJAX request to retrieve the notifications
		 */
		_fetch: function() {
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
					this._shutDownNotifications();
				} else if (!_.isUndefined(data) && !_.isUndefined(data.ocs) && !_.isUndefined(data.ocs.data) && _.isArray(data.ocs.data)) {
					this.vm.notifications = data.ocs.data;
				} else {
					console.debug("data.ocs.data is undefined or not an array");
				}
			}.bind(this));
			request.fail(function(xhr) {
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
			}.bind(this));
		},

		_backgroundFetch: function() {
			this.vm.backgroundFetching = true;
			this._fetch();
		},

		/**
		 * The app was disabled or has no notifiers, so we can stop polling
		 * And hide the UI as well
		 */
		_shutDownNotifications: function() {
			window.clearInterval(this.interval);
			this.vm.shutdown = true;
		}
	};
})(OC, OCA, $, _);

$(document).ready(function () {
	OCA.Notifications.App.initialise();
});
