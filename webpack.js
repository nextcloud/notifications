const webpackConfig = require('@nextcloud/webpack-vue-config')
const webpackRules = require('@nextcloud/webpack-vue-config/rules')

const BabelLoaderExcludeNodeModulesExcept = require('babel-loader-exclude-node-modules-except')

// Edit JS rule
webpackRules.RULE_JS.exclude = BabelLoaderExcludeNodeModulesExcept([
	'@juliushaertl/vue-richtext',
	'@nextcloud/event-bus',
	'semver',
])

// Replaces rules array
webpackConfig.module.rules = Object.values(webpackRules)

module.exports = webpackConfig
