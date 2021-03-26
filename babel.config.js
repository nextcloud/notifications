module.exports = {
	plugins: ['add-module-exports'],
	presets: [
		[
			'@babel/preset-env',
			{
				corejs: 3,
				useBuiltIns: 'entry',
				modules: 'commonjs',
			},
		],
	],
}
