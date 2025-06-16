<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcButton
		v-if="isWebLink"
		variant="primary"
		class="action-button pull-right"
		:href="link"
		@click="onClickActionButtonWeb">
		{{ label }}
	</NcButton>
	<NcButton
		v-else
		:variant="primary ? 'primary' : 'secondary'"
		class="action-button pull-right"
		@click="onClickActionButton">
		{{ label }}
	</NcButton>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'
import { emit } from '@nextcloud/event-bus'
import { t } from '@nextcloud/l10n'
import NcButton from '@nextcloud/vue/components/NcButton'

export default {
	name: 'ActionButton',

	components: {
		NcButton,
	},

	props: {
		label: {
			type: String,
			default: '',
			required: true,
		},

		link: {
			type: String,
			default: '',
			required: true,
		},

		type: {
			type: String,
			default: '',
			required: true,
		},

		primary: {
			type: Boolean,
			default: false,
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
			return this.type || 'GET'
		},
	},

	methods: {
		async onClickActionButtonWeb(e) {
			try {
				const event = {
					cancelAction: false,
					notification: this.$parent.$props,
					action: {
						url: this.link,
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
						url: this.link,
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
					url: this.link,
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
