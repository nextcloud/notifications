<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div class="mention">
		<NcUserBubble
			v-if="!cloudId"
			:display-name="name"
			:user="id" />
		<strong
			v-else
			:title="cloudId">
			{{ name }}
		</strong>
	</div>
</template>

<script>

import NcUserBubble from '@nextcloud/vue/components/NcUserBubble'

export default {
	name: 'UserParameter',

	components: {
		NcUserBubble,
	},

	props: {
		// eslint-disable-next-line vue/no-unused-properties
		type: {
			type: String,
			required: true,
		},

		id: {
			type: String,
			required: true,
		},

		name: {
			type: String,
			required: true,
		},

		server: {
			type: String,
			default: '',
		},
	},

	computed: {
		cloudId() {
			return this.server ? (this.id + '@' + this.server) : ''
		},
	},
}
</script>

<style lang="scss" scoped>
.mention {
	display: contents;
	white-space: nowrap;
}
</style>
