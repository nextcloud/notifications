<template>
	<button class="action-button pull-right"
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

	methods: {
		async onClickActionButton() {
			const type = this.type || 'GET'
			if (type === 'WEB') {
				window.location = this.link
				return
			}

			try {
				// execute action
				await axios({
					method: type,
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
							type: type,
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
