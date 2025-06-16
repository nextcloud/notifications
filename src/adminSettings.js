/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import AdminSettings from './views/AdminSettings.vue'

// Styles
import '@nextcloud/dialogs/style.css'

Vue.prototype.t = t
Vue.prototype.n = n

export default new Vue({
	el: '#notifications-admin-settings',
	render: (h) => h(AdminSettings),
})
