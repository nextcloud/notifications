<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script setup>
import { computed } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import { isSafeLink } from '../utils/link.js'

/**
 * @typedef {object} NotificationAction
 * @property {string} label action label (required)
 * @property {string} link action link (required)
 * @property {string} type action type (required)
 * @property {boolean} primary action primary (required)
 */

const props = defineProps({
	action: {
		/** @type {import('vue').PropType<NotificationAction>} */
		type: Object,
		required: true,
	},
})

const emit = defineEmits(['click'])

const isWebLink = computed(() => props.action.type === 'WEB' && isSafeLink(props.action.link))

/**
 * Emits a click event with the action details
 *
 * @param {MouseEvent} event Mouse click event
 */
function onClickActionButton(event) {
	const action = {
		url: props.action.link,
		type: props.action.type || 'GET',
	}
	emit('click', { event, action })
}
</script>

<template>
	<NcButton
		:variant="(isWebLink || action.primary) ? 'primary' : 'secondary'"
		:href="isWebLink ? action.link : undefined"
		class="action-button pull-right"
		@click="onClickActionButton">
		{{ action.label }}
	</NcButton>
</template>
