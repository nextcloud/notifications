/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp, defineAsyncComponent } from 'vue'
import BrowserStorage from './services/BrowserStorage.js'

// Styles
import './styles/styles.scss'
import '@nextcloud/dialogs/style.css'

// TODO: remove in v37. Notifications were cached in the browser storage before v36
for (const key of ['status', 'headers', 'data', 'tabId', 'lastUpdated']) {
	BrowserStorage.removeItem(key)
}

const NotificationsApp = defineAsyncComponent(() => import('./NotificationsApp.vue'))
export default createApp(NotificationsApp).mount('#notifications')
