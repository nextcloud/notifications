const webpackConfig = require('@nextcloud/webpack-vue-config')
const BabelLoaderExcludeNodeModulesExcept = require('babel-loader-exclude-node-modules-except')

const jsRule = webpackConfig.module.rules.findIndex(t => (t.test.toString() === /\.js$/.toString()))

webpackConfig.module.rules[jsRule].exclude = BabelLoaderExcludeNodeModulesExcept([
	'@juliushaertl/vue-richtext',
	'@nextcloud/event-bus',
	'semver',
])
webpackConfig.module.rules[jsRule].options = {
	plugins: ['add-module-exports'],
	presets: [
		/**
		 * From "add-module-exports" documentation:
		 * "webpack doesn't perform commonjs transformation for
		 * codesplitting. Need to set commonjs conversion."
		 */
		['@babel/env', { modules: 'commonjs' }],
	],
}

// Merge rules by replacing existing tests
module.exports = webpackConfig
