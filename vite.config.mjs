/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createAppConfig } from '@nextcloud/vite-config'
import { join, resolve } from 'node:path'

export default createAppConfig({
	main: resolve(join('src', 'main.js')),
	settings: resolve(join('src', 'settings.js')),
	'admin-settings': resolve(join('src', 'adminSettings.js')),
}, {
	emptyOutputDirectory: {
		additionalDirectories: ['css'],
	},
	extractLicenseInformation: {
		overwriteLicenses: {
			'@nextcloud/axios': 'GPL-3.0-or-later',
		},
		includeSourceMaps: true,
	},
	config: {
		build: {
			rollupOptions: {
				output: {
					manualChunks: (id) => {
						// By default, Vite stores __vitePreload in the entrypoint
						// Then chunks import entrypoint to get the _vitePreload function
						// Which results not only in cyclic import but also duplicated module in production
						// Because in production entrypoints must be imported with ?v=hash cache busting
						// See: https://github.com/nextcloud/notifications/issues/2164
						//
						// To avoid it - explicitly exclude the preload helper to a separate chunk
						//
						// TODO: add to @nextcloud/vite-config
						if (id.startsWith('\0vite/preload-helper')) {
							return 'vite-preload-helper'
						}
					},
				},
			},
		},
	},
})
