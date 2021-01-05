const webpackConfig = require('@nextcloud/webpack-vue-config')
const BabelLoaderExcludeNodeModulesExcept = require('babel-loader-exclude-node-modules-except')

const jsRule = webpackConfig.module.rules.findIndex(t => (t.test.toString() === /\.js$/.toString()))

webpackConfig.module.rules[jsRule].exclude = BabelLoaderExcludeNodeModulesExcept([
	'@juliushaertl/vue-richtext',
	'@nextcloud/event-bus',
	'is-absolute-url',
	'is-plain-obj',
	'semver',
])
webpackConfig.module.rules.push({
	test: /\.mjs$/,
	loader: 'babel-loader',
	exclude: BabelLoaderExcludeNodeModulesExcept([
		'rehype-add-classes',
	]),
})

// Merge rules by replacing existing tests
module.exports = webpackConfig
