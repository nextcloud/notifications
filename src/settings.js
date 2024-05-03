/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import UserSettings from './views/UserSettings.vue'

// Styles
import '@nextcloud/dialogs/style.css'

Vue.prototype.t = t
Vue.prototype.n = n

export default new Vue({
	el: '#notifications-user-settings',
	render: h => h(UserSettings),
})
