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
			'no-console': 'off',
		},
	},
]
