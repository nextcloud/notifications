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
		v-else-if="link"
		:href="link"
		class="external"
		target="_blank"
		rel="noopener noreferrer">
		<strong>{{ name }}</strong>
	</a>
	<strong v-else>{{ name }}</strong>
</template>

<script>
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
		hasInternalLink() {
			return this.link && (
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
