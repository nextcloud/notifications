/**
 * @copyright (c) 2016 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

/* global OC, _, t, define */

define(function () {
	"use strict";

	return {
		avatarsEnabled: true,

		fileTemplate: require('./templates/file.handlebars'),

		userLocalTemplate: require('./templates/userLocal.handlebars'),
		userRemoteTemplate: require('./templates/userRemote.handlebars'),

		unknownTemplate: require('./templates/unkown.handlebars'),
		unknownLinkTemplate: require('./templates/unkownLink.handlebars'),

		/**
		 * @param {string} message
		 * @param {Object} parameters
		 * @returns {string}
		 */
		parseMessage: function(message, parameters) {
			message = escapeHTML(message);
			var self = this,
				regex = /\{([a-z\-_0-9]+)\}/gi,
				matches = message.match(regex);

			_.each(matches, function(parameter) {
				parameter = parameter.substring(1, parameter.length - 1);
				var parsed = self.parseParameter(parameters[parameter]);

				message = message.replace('{' + parameter + '}', parsed);
			});

			return message.replace(new RegExp("\n", 'g'), '<br>');
		},

		/**
		 * @param {Object} parameter
		 * @param {string} parameter.type
		 * @param {string} parameter.id
		 * @param {string} parameter.name
		 * @param {string} parameter.link
		 */
		parseParameter: function(parameter) {
			switch (parameter.type) {
				case 'file':
					return this.parseFileParameter(parameter);

				case 'user':
					if (_.isUndefined(parameter.server)) {
						return this.userLocalTemplate(parameter);
					}


					return this.userRemoteTemplate(parameter);

				default:
					if (!_.isUndefined(parameter.link)) {
						return this.unknownLinkTemplate(parameter);
					}

					return this.unknownTemplate(parameter);
			}
		},

		/**
		 * @param {Object} parameter
		 * @param {string} parameter.type
		 * @param {string} parameter.id
		 * @param {string} parameter.name
		 * @param {string} parameter.path
		 * @param {string} parameter.link
		 */
		parseFileParameter: function(parameter) {
			var lastSlashPosition = parameter.path.lastIndexOf('/');
			var firstSlashPosition = parameter.path.indexOf('/');
			parameter.path = parameter.path.substring(firstSlashPosition === 0 ? 1 : 0, lastSlashPosition);

			return this.fileTemplate(_.extend(parameter, {
				title: parameter.path.length === 0 ? '' : t('notifications', 'in {path}', parameter)
			}));
		}
	};
});
