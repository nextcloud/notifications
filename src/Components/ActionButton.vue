<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcButton
		:variant="(isWebLink || action.primary) ? 'primary' : 'secondary'"
		:href="isWebLink ? action.link : undefined"
		class="action-button pull-right"
		@click="onClickActionButton">
		{{ action.label }}
	</NcButton>
</template>

<script>
import NcButton from '@nextcloud/vue/components/NcButton'

/**
 * @typedef {object} NotificationAction
 * @property {string} label action label (required)
 * @property {string} link action link (required)
 * @property {string} type action type (required)
 * @property {boolean} primary action primary (required)
 */

export default {
	name: 'ActionButton',

	components: {
		NcButton,
	},

	props: {
		action: {
			/** @type {import('vue').PropType<NotificationAction>} */
			type: Object,
			required: true,
		},
	},

	emits: ['remove'],

	computed: {
		isWebLink() {
			return this.action.type === 'WEB'
		},
	},

	methods: {
		onClickActionButton(event) {
			const action = {
				url: this.action.link,
				type: this.action.type || 'GET',
			}
			this.$emit('click', { event, action })
		},
	},
}
</script>
