<template>
	<button class="action-button pull-right" :class="{ primary: primary }"
		:data-type="type" :data-href="link" @click="onClickActionButton">
		{{ label }}
	</button>
</template>

<script>
import axios from '@nextcloud/axios'
export default {
	name: 'Action',

	props: {
		label: {
			type: String,
			default: '',
			required: true
		},
		link: {
			type: String,
			default: '',
			required: true
		},
		type: {
			type: String,
			default: '',
			required: true
		},
		primary: {
			type: Boolean,
			default: false,
			required: true
		}
	},

	methods: {
		onClickActionButton: function() {
			const type = this.type || 'GET'
			if (type === 'WEB') {
				OC.redirect(this.link)
				return
			}

			axios({
				method: type,
				url: this.link
			})
				.then(() => {
					this.$parent._$el.fadeOut(OC.menuSpeed)
					this.$parent.$emit('remove')
					$('body').trigger(new $.Event('OCA.Notification.Action', {
						notification: this.$parent,
						action: {
							url: this.link,
							type: type
						}
					}))
				})
				.catch(() => {
					OC.Notification.showTemporary(t('notifications', 'Failed to perform action'))
				})
		}
	}
}
</script>
