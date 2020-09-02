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
				test: /\.scss$/,
				use: ['vue-style-loader', 'css-loader', 'sass-loader'],
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				exclude: babelLoaderExcludeNodeModulesExcept([
					'@juliushaertl/vue-richtext',
					'vue-material-design-icons',
				]),
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: babelLoaderExcludeNodeModulesExcept([
					'@juliushaertl/vue-richtext',
					'@nextcloud/event-bus',
					'semver',
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
	plugins: [
		new VueLoaderPlugin(),
	],
	resolve: {
		extensions: ['*', '.js', '.vue'],
		symlinks: false,
	},
}
