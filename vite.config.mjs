/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { join, resolve } from 'node:path'
import { createAppConfig } from '@nextcloud/vite-config'

export default createAppConfig({
	main: resolve(join('src', 'main.js')),
	settings: resolve(join('src', 'settings.js')),
	'admin-settings': resolve(join('src', 'adminSettings.js')),
}, {
	emptyOutputDirectory: {
		additionalDirectories: [
			'css',
		],
	},
	extractLicenseInformation: {
		overwriteLicenses: {
			'@nextcloud/axios': 'GPL-3.0-or-later',
		},
		includeSourceMaps: true,
	},
})
