<template>
	<a v-if="isWebLink"
		class="button action-button pull-right"
		:class="{ primary: primary, 'button--tabbed': this.tabbed }"
		:href="link"
		@keydown.enter="makeActive"
		@keyup.enter="makeInactive"
		@click="handleClick"
		@blur="handleBlur"
		@keyup.tab.exact="handleTabUp"
		@keyup.shift.tab="handleTabUp">
		{{ label }}
	</a>
	<Button v-else-if="!isWebLink"
		:type="buttonType"
		class="action-button pull-right"
		@click="onClickActionButton">
		{{ label }}
	</Button>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'
import Button from '@nextcloud/vue/dist/Components/Button'

export default {
	name: 'Action',

	components: {
		Button,
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

		buttonType() {
			return this.primary ? 'primary' : 'secondary'
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

		/**
		 * Removes the tabbed state of the button.
		 */
		handleClick() {
			this.tabbed = false
		},
		/**
		 * When the tab key is lifted, the button has been "tabbed in",
		 * see comments on the `tabbed` variable declared in the data.
		 */
		handleTabUp() {
			this.tabbed = true
		},
		/**
		 * Everytime the button is blurred, we remove the tabbed state.
		 */
		handleBlur() {
			this.tabbed = false
		},
		/**
		 * When the button is reached via keyboard navigation and pressed using
		 * the enter key, we slightly change the styles to provide an "active-like"
		 * feedback. When using the mouse this is achieved with the ripple effect.
		 */
		makeActive() {
			this.tabbed = false
		},
		makeInactive() {
			this.tabbed = true
		},
	},
}
</script>
