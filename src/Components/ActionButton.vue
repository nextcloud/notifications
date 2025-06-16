<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcButton
		v-if="isWebLink"
		variant="primary"
		class="action-button pull-right"
		:href="action.link"
		@click="onClickActionButtonWeb">
		{{ action.label }}
	</NcButton>
	<NcButton
		v-else
		:variant="action.primary ? 'primary' : 'secondary'"
		class="action-button pull-right"
		@click="onClickActionButton">
		{{ action.label }}
	</NcButton>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'
import { emit } from '@nextcloud/event-bus'
import { t } from '@nextcloud/l10n'
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
			/** @type {ObjectConstructor<NotificationAction>} */
			type: Object,
			required: true,
		},

		notificationIndex: {
			type: Number,
			required: true,
		},
	},

	data() {
		return {
			tabbed: false,
		}
	},

	computed: {
		isWebLink() {
			return this.typeWithDefault === 'WEB'
		},

		typeWithDefault() {
			return this.action.type || 'GET'
		},
	},

	methods: {
		async onClickActionButtonWeb(e) {
			try {
				const event = {
					cancelAction: false,
					notification: this.$parent.$props,
					action: {
						url: this.action.link,
						type: this.typeWithDefault,
					},
				}
				await emit('notifications:action:execute', event)

				if (event.cancelAction) {
					// Action cancelled by event
					e.preventDefault()
				}
			} catch (error) {
				console.error('Failed to perform action', error)
				showError(t('notifications', 'Failed to perform action'))
			}
		},

		async onClickActionButton() {
			try {
				const event = {
					cancelAction: false,
					notification: this.$parent.$props,
					action: {
						url: this.action.link,
						type: this.typeWithDefault,
					},
				}
				await emit('notifications:action:execute', event)

				if (event.cancelAction) {
					// Action cancelled by event
					return
				}

				// execute action
				await axios({
					method: this.typeWithDefault,
					url: this.action.link,
				})

				// emit event to current app
				this.$parent.$emit('remove', this.notificationIndex)

				emit('notifications:action:executed', event)
			} catch (error) {
				console.error('Failed to perform action', error)
				showError(t('notifications', 'Failed to perform action'))
			}
		},
	},
}
</script>
