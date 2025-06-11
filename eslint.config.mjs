/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { recommendedVue2Javascript } from '@nextcloud/eslint-config'

export default [
	...recommendedVue2Javascript,
	// Disabled rules from recommendedVue2Javascript pack
	{
		rules: {
			'@nextcloud/vue/no-deprecated-exports': 'off',
			'@nextcloud/vue/no-deprecated-props': 'off',
			'@stylistic/arrow-parens': 'off',
			'antfu/top-level-function': 'off',
			'jsdoc/tag-lines': 'off',
			'no-console': 'off',
			'no-unused-vars': 'off',
			'no-use-before-define': 'off',
			'vue/define-macros-order': 'off',
			'vue/first-attribute-linebreak': 'off',
			'vue/multi-word-component-names': 'off',
			'vue/no-boolean-default': 'off',
			'vue/no-required-prop-with-default': 'off',
			'vue/no-unused-properties': 'off',
			'vue/no-unused-refs': 'off',
		},
	},
]
