/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'

// Styles
import './styles/styles.scss'
import '@nextcloud/dialogs/style.css'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

// Hotfix to prevent creating 2 apps on production
// See: https://github.com/nextcloud/notifications/issues/2164
if (!window._nc_notifications_app_initialized) {
	window._nc_notifications_app_initialized = true

	// eslint-disable-next-line no-new
	new Vue({
		el: '#notifications',
		// eslint-disable-next-line vue/match-component-file-name
		name: 'NotificationsApp',
		components: {
			NotificationsApp: () => import('./NotificationsApp.vue'),
		},
		render: h => h('NotificationsApp'),
	})
}
