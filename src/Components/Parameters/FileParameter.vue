<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<a
		:title="title"
		class="filename"
		:href="link">{{ name }}</a>
</template>

<script>
import { t } from '@nextcloud/l10n'

export default {
	name: 'FileParameter',

	props: {
		// eslint-disable-next-line vue/no-unused-properties
		type: {
			type: String,
			required: true,
		},

		// eslint-disable-next-line vue/no-unused-properties
		id: {
			type: [Number, String],
			required: true,
		},

		name: {
			type: String,
			required: true,
		},

		path: {
			type: String,
			default: '',
		},

		link: {
			type: String,
			default: '',
		},
	},

	computed: {
		title() {
			const lastSlashPosition = this.path.lastIndexOf('/')
			const firstSlashPosition = this.path.indexOf('/')
			const path = this.path.substring(firstSlashPosition === 0 ? 1 : 0, lastSlashPosition)

			if (path.length === 0) {
				return ''
			}

			return t('notifications', 'in {path}', {
				path,
			})
		},
	},
}
</script>
