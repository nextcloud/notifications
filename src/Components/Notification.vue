<template>
	<div class="notification" :data-id="notificationId" :data-timestamp="timestamp">
		<div class="notification-heading">
			<span
				v-tooltip.bottom="absoluteDate"
				class="notification-time live-relative-timestamp"
				:data-timestamp="timestamp">{{ relativeDate }}</span>
			<div class="notification-delete" @click="onDismissNotification">
				<span class="icon icon-close svg" :title="t('notifications', 'Dismiss')" />
			</div>
		</div>
		<a v-if="useLink" :href="link" class="notification-subject full-subject-link">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon"></span>
			<RichText
				:text="subjectRich"
				:arguments="preparedSubjectParameters" />
		</a>
		<div v-else class="notification-subject">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon"></span>
			<RichText
				:text="subjectRich"
				:arguments="preparedSubjectParameters" />
		</div>
		<div v-if="message" class="notification-message" @click="onClickMessage">
			<div class="message-container" :class="{ collapsed: isCollapsedMessage }">
				<RichText
					:text="messageRich"
					:arguments="preparedMessageParameters" />
			</div>
			<div v-if="isCollapsedMessage" class="notification-overflow" />
		</div>
		<div v-if="actions.length" class="notification-actions">
			<Action v-for="(a, i) in actions" :key="i" v-bind="a" />
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip'
import Action from './Action'
import { generateOcsUrl } from '@nextcloud/router'
import RichText from '@juliushaertl/vue-richtext'
import DefaultParameter from './Parameters/DefaultParameter'
import File from './Parameters/File'
import User from './Parameters/User'

export default {
	name: 'Notification',

	components: {
		Action,
		RichText,
	},

	directives: {
		tooltip: Tooltip,
	},

	props: {
		notificationId: {
			type: Number,
			default: -1,
			required: true,
		},
		datetime: {
			type: String,
			default: '',
			required: true,
		},
		app: {
			type: String,
			default: '',
			required: true,
		},
		icon: {
			type: String,
			default: '',
			required: true,
		},
		link: {
			type: String,
			default: '',
			required: true,
		},
		user: {
			type: String,
			default: '',
			required: true,
		},
		message: {
			type: String,
			default: '',
			required: true,
		},
		messageRich: {
			type: String,
			default: '',
			required: true,
		},
		messageRichParameters: {
			type: [Object, Array],
			default() {
				return {}
			},
			required: true,
		},
		subject: {
			type: String,
			default: '',
			required: true,
		},
		subjectRich: {
			type: String,
			default: '',
			required: true,
		},
		subjectRichParameters: {
			type: [Object, Array],
			default() {
				return {}
			},
			required: true,
		},
		objectType: {
			type: String,
			default: '',
			required: true,
		},
		objectId: {
			type: String,
			default: '',
			required: true,
		},
		actions: {
			type: Array,
			default() {
				return []
			},
			required: true,
		},

		index: {
			type: Number,
			default: -1,
			required: true,
		},
	},

	data() {
		return {
			showFullMessage: false,
		}
	},

	computed: {
		timestamp: function() {
			return (new Date(this.datetime)).valueOf()
		},
		absoluteDate: function() {
			return OC.Util.formatDate(this.timestamp, 'LLL')
		},
		relativeDate: function() {
			return OC.Util.relativeModifiedDate(this.timestamp)
		},
		useLink: function() {
			if (!this.link) {
				return false
			}

			let parametersHaveLink = false
			Object.keys(this.subjectRichParameters).forEach(p => {
				if (p.link) {
					parametersHaveLink = true
				}
			})
			return parametersHaveLink
		},

		preparedSubjectParameters() {
			return this.prepareParameters(this.subjectRichParameters)
		},

		preparedMessageParameters() {
			return this.prepareParameters(this.messageRichParameters)
		},

		isCollapsedMessage: function() {
			return this.message.length > 200 && !this.showFullMessage
		},
	},

	mounted: function() {
		// Parents: TransitionGroup > NotificationsList
		if (this.$parent.$parent.showBrowserNotifications) {
			this._createWebNotification()
		}
	},

	methods: {
		prepareParameters(parameters) {
			const richParameters = {}
			Object.keys(parameters).forEach(p => {
				const type = parameters[p].type
				if (type === 'user') {
					richParameters[p] = {
						component: User,
						props: parameters[p],
					}
				} else if (type === 'file') {
					richParameters[p] = {
						component: File,
						props: parameters[p],
					}
				} else {
					richParameters[p] = {
						component: DefaultParameter,
						props: parameters[p],
					}
				}
			})
			return richParameters
		},

		onClickMessage: function(e) {
			if (e.target.classList.contains('rich-text--wrapper')) {
				this.showFullMessage = !this.showFullMessage
			}
		},

		onDismissNotification: function() {
			axios
				.delete(generateOcsUrl('apps/notifications/api/v2', 2) + 'notifications/' + this.notificationId)
				.then(() => {
					this.$emit('remove', this.index)
				})
				.catch(() => {
					OC.Notification.showTemporary(t('notifications', 'Failed to dismiss notification'))
				})
		},

		/**
		 * Create a browser notification
		 * @see https://developer.mozilla.org/en/docs/Web/API/notification
		 */
		_createWebNotification: function() {
			const n = new Notification(this.subject, {
				title: this.subject,
				lang: OC.getLocale(),
				body: this.message,
				icon: this.icon,
				tag: this.notificationId,
			})

			if (this.link) {
				n.onclick = function(event) {
					event.preventDefault()
					window.location.href = this.link
				}.bind(this)
			}

			setTimeout(n.close.bind(n), 5000)
		},
	},
}
</script>

<style lang="scss" scoped>
::v-deep .rich-text--wrapper {
	white-space: pre-wrap;
	word-break: break-word;
}
</style>
