const { merge } = require('webpack-merge')
const webpackConfig = require('@nextcloud/webpack-vue-config')
const BabelLoaderExcludeNodeModulesExcept = require('babel-loader-exclude-node-modules-except')

const config = {
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				exclude: BabelLoaderExcludeNodeModulesExcept([
					'@juliushaertl/vue-richtext',
					'vue-material-design-icons',
				]),
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: BabelLoaderExcludeNodeModulesExcept([
					'@juliushaertl/vue-richtext',
					'@nextcloud/event-bus',
					'semver',
				]),
			},
		],
	},
}

const mergedConfigs = merge(config, webpackConfig)

// Remove duplicate rules by the `test` key
mergedConfigs.module.rules = mergedConfigs.module.rules.filter((v, i, a) => a.findIndex(t => (t.test.toString() === v.test.toString())) === i)

// Merge rules by replacing existing tests
module.exports = mergedConfigs
