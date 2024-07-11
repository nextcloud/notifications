/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
const webpackConfig = require('@nextcloud/webpack-vue-config')
// eslint-disable-next-line n/no-extraneous-require
const TerserPlugin = require('terser-webpack-plugin')
const WebpackSPDXPlugin = require('./build-js/WebpackSPDXPlugin.js')
const webpackRules = require('@nextcloud/webpack-vue-config/rules')
const path = require('path')

const BabelLoaderExcludeNodeModulesExcept = require('babel-loader-exclude-node-modules-except')

// Edit JS rule
webpackRules.RULE_JS.exclude = BabelLoaderExcludeNodeModulesExcept([
	'@nextcloud/vue-richtext',
	'@nextcloud/event-bus',
	'semver',
])

// Replaces rules array
webpackConfig.module.rules = Object.values(webpackRules)

webpackConfig.entry = {
	main: path.resolve(path.join('src', 'main.js')),
	settings: path.resolve(path.join('src', 'settings.js')),
	'admin-settings': path.resolve(path.join('src', 'adminSettings.js')),
}

webpackConfig.optimization.minimizer = [new TerserPlugin({
	extractComments: false,
	terserOptions: {
		format: {
			comments: false,
		},
	},
})]

webpackConfig.plugins = [
	...webpackConfig.plugins,
	// Generate reuse license files
	new WebpackSPDXPlugin({
		override: {
			// TODO: Remove if they fixed the license in the package.json
			'@nextcloud/axios': 'GPL-3.0-or-later',
			'@nextcloud/vue': 'AGPL-3.0-or-later',
			'nextcloud-vue-collections': 'AGPL-3.0-or-later',
		}
	}),
]
module.exports = webpackConfig
