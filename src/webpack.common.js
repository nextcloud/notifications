const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')
const babelLoaderExcludeNodeModulesExcept = require('babel-loader-exclude-node-modules-except')

module.exports = {
	entry: path.join(__dirname, 'Init.js'),
	output: {
		path: path.resolve(__dirname, '../js'),
		publicPath: '/js/',
		filename: 'notifications.js',
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ['vue-style-loader', 'css-loader'],
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				options: {
					hotReload: false, // disables Hot Reload
				},
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: babelLoaderExcludeNodeModulesExcept([
					'semver',
					'@nextcloud/event-bus',
				]),
			},
			{
				enforce: 'pre',
				test: /\.(js|vue)$/,
				loader: 'eslint-loader',
				exclude: /node_modules/,
			},
			{
				test: /\.(png|jpg|gif|svg)$/,
				loader: 'file-loader',
				options: {
					name: '[name].[ext]?[hash]',
				},
			},
			{
				test: /\.handlebars/,
				loader: 'handlebars-loader',
				options: {
					extensions: '.handlebars',
				},
			},
		],
	},
	plugins: [new VueLoaderPlugin()],
	resolve: {
		alias: {
			vue$: 'vue/dist/vue.esm.js',
		},
		extensions: ['*', '.js', '.vue', '.json'],
	},
}
