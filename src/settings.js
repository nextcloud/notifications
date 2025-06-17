/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import UserSettings from './views/UserSettings.vue'

// Styles
import '@nextcloud/dialogs/style.css'

export default createApp(UserSettings).mount('#notifications-user-settings')
