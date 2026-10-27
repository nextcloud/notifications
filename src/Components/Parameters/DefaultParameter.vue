<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<a
		v-if="hasInternalLink"
		:href="link">
		<strong>{{ name }}</strong>
	</a>
	<a
		v-else-if="hasSafeLink"
		:href="link"
		class="external"
		target="_blank"
		rel="noopener noreferrer">
		<strong>{{ name }}</strong>
	</a>
	<strong v-else>{{ name }}</strong>
</template>

<script>
import { isSafeLink } from '../../utils/link.js'

export default {
	name: 'DefaultParameter',
	props: {
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

		link: {
			type: String,
			default: '',
		},
	},

	computed: {
		hasSafeLink() {
			return isSafeLink(this.link)
		},

		hasInternalLink() {
			return this.hasSafeLink && (
				this.type === 'deck-board'
				|| this.type === 'deck-card'
			)
		},
	},
}
</script>

<style lang="scss" scoped>
.external:after {
	content: ' ↗';
}
</style>
