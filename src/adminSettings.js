/**
 * @copyright Copyright (c) 2022 Nikita Toponen <natoponen@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import Vue from 'vue'
import AdminSettings from './views/AdminSettings.vue'

// Styles
import '@nextcloud/dialogs/dist/index.css'

Vue.prototype.t = t
Vue.prototype.n = n

export default new Vue({
	el: '#notifications-admin-settings',
	render: h => h(AdminSettings),
})
