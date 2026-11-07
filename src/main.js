/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp, defineAsyncComponent } from 'vue'

// Styles
import './styles/styles.scss'

const NotificationsApp = defineAsyncComponent(() => import('./NotificationsApp.vue'))
export default createApp(NotificationsApp).mount('#notifications')
