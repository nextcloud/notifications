/**
 * @copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

/* global OC, $, define */

define(function () {
	"use strict";

	return {
		/** @type {string} */
		template: '' +
		'<button' +
		'  class="action-button pull-right"' +
		'  :class="{ primary: primary }"' +
		'  :data-type="type" ' +
		'  :data-href="link" @click="onClickActionButton">{{ label }}</button>',

		props: [
			'label',
			'link',
			'type',
			'primary'
		],

		methods: {
			onClickActionButton: function () {
				$.ajax({
					url: this.link,
					type: this.type || 'GET',
					success: function () {
						this.$parent._$el.fadeOut(OC.menuSpeed);
						this.$parent.$emit('remove');
						// $('body').trigger(new $.Event('OCA.Notification.Action', {
						// 	notification: this.$parent,
						// 	action: {
						// 		url: this.link,
						// 		type: this.type || 'GET'
						// 	}
						// }));
					}.bind(this),
					error: function () {
						OC.Notification.showTemporary('Failed to perform action'); // FIXME translation
					}
				});
			}
		}
	};
});
