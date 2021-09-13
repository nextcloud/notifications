<template>
	<a v-if="isWebLink"
		class="button action-button pull-right"
		:class="{ primary: primary }"
		:href="link">
		{{ label }}
	</a>
	<button v-else
		class="action-button pull-right"
		:class="{ primary: primary }"
		:data-type="type"
		:data-href="link"
		@click="onClickActionButton">
		{{ label }}
	</button>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'Action',

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
		async onClickActionButton() {
			try {
				// execute action
				await axios({
					method: this.typeWithDefault,
					url: this.link,
				})

				// emit event to current app
				this.$parent._$el.fadeOut(OC.menuSpeed)
				this.$parent.$emit('remove')
				try {
					$('body').trigger(new $.Event('OCA.Notification.Action', {
						notification: this.$parent,
						action: {
							url: this.link,
							type: this.typeWithDefault,
						},
					}))
				// do not do anything but log, the action went fine
				// only the event bus listener failed, this is not our problem
				} catch (error) {
					console.error(error)
				}
			} catch (error) {
				console.error('Failed to perform action', error)
				showError(t('notifications', 'Failed to perform action'))
			}
		},
	},
}
</script>
