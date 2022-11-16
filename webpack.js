const webpackConfig = require('@nextcloud/webpack-vue-config')
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

module.exports = webpackConfig
