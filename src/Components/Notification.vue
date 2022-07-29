<template>
	<li class="notification" :data-id="notificationId" :data-timestamp="timestamp">
		<div class="notification-heading">
			<span v-tooltip.bottom="absoluteDate"
				class="notification-time live-relative-timestamp"
				:data-timestamp="timestamp">{{ relativeDate }}</span>
			<Button class="notification-dismiss-button"
				type="tertiary"
				:aria-label="t('notifications', 'Dismiss')"
				@click="onDismissNotification">
				<template #icon>
					<Close :size="20" />
				</template>
			</Button>
		</div>

		<a v-if="useLink" :href="link" class="notification-subject full-subject-link">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon" alt=""></span>
			<RichText v-if="subjectRich"
				:text="subjectRich"
				:arguments="preparedSubjectParameters" />
			<span v-else class="subject">{{ subject }}</span>
		</a>
		<div v-else class="notification-subject">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon" alt=""></span>
			<RichText v-if="subjectRich"
				:text="subjectRich"
				:arguments="preparedSubjectParameters" />
			<span v-else class="subject">{{ subject }}</span>
		</div>

		<div v-if="message" class="notification-message" @click="onClickMessage">
			<div class="message-container" :class="{ collapsed: isCollapsedMessage }">
				<RichText v-if="messageRich"
					:text="messageRich"
					:arguments="preparedMessageParameters"
					:autolink="true" />
				<span v-else>{{ message }}</span>
			</div>
			<div v-if="isCollapsedMessage" class="notification-overflow" />
		</div>

		<div v-if="actions.length" class="notification-actions">
			<Action v-for="(a, i) in actions" :key="i" v-bind="a" />
		</div>
	</li>
</template>

<script>
import axios from '@nextcloud/axios'
import Button from '@nextcloud/vue/dist/Components/Button'
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip'
import Close from 'vue-material-design-icons/Close'
import { showError } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { Howl } from 'howler'
import Action from './Action'
import { generateOcsUrl, generateFilePath } from '@nextcloud/router'
import moment from '@nextcloud/moment'
import RichText from '@juliushaertl/vue-richtext'
import DefaultParameter from './Parameters/DefaultParameter'
import File from './Parameters/File'
import User from './Parameters/User'

export default {
	name: 'Notification',

	components: {
		Action,
		Button,
		Close,
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

	_$el: null,

	computed: {
		timestamp() {
			return (new Date(this.datetime)).valueOf()
		},
		absoluteDate() {
			return moment(this.timestamp).format('LLL')
		},
		relativeDate() {
			const diff = moment().diff(moment(this.timestamp))
			if (diff >= 0 && diff < 45000) {
				return t('core', 'seconds ago')
			}
			return moment(this.timestamp).fromNow()
		},
		useLink() {
			if (!this.link) {
				return false
			}

			let parametersHaveLink = false
			Object.keys(this.subjectRichParameters).forEach(p => {
				if (p.link) {
					parametersHaveLink = true
				}
			})
			return !parametersHaveLink
		},

		preparedSubjectParameters() {
			return this.prepareParameters(this.subjectRichParameters)
		},

		preparedMessageParameters() {
			return this.prepareParameters(this.messageRichParameters)
		},

		isCollapsedMessage() {
			return this.message.length > 200 && !this.showFullMessage
		},
	},

	mounted() {
		this._$el = $(this.$el)

		// Parents: TransitionGroup > Transition > HeaderMenu
		if (typeof this.$parent.$parent.$parent.showBrowserNotifications === 'undefined') {
			console.error('Failed to read showBrowserNotifications property from App component')
		}

		if (this.$parent.$parent.showBrowserNotifications) {
			this._createWebNotification()

			if (this.app === 'spreed' && this.objectType === 'call') {
				if (loadState('notifications', 'sound_talk')) {
					const sound = new Howl({
						src: [
							generateFilePath('notifications', 'img', 'talk.ogg'),
						],
						volume: 0.5,
					})

					sound.play()
				}
			} else if (loadState('notifications', 'sound_notification')) {
				const sound = new Howl({
					src: [
						generateFilePath('notifications', 'img', 'notification.ogg'),
					],
					volume: 0.5,
				})

				sound.play()
			}
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

		onClickMessage(e) {
			if (e.target.classList.contains('rich-text--wrapper')) {
				// Vue RichText
				this.showFullMessage = !this.showFullMessage
			} else if (!this.messageRich && !!this.message) {
				// Plain text
				this.showFullMessage = !this.showFullMessage
			}
		},

		onDismissNotification() {
			axios
				.delete(generateOcsUrl('apps/notifications/api/v2/notifications/{id}', { id: this.notificationId }))
				.then(() => {
					this.$emit('remove', this.index)
				})
				.catch(() => {
					showError(t('notifications', 'Failed to dismiss notification'))
				})
		},

		/**
		 * Create a browser notification
		 *
		 * @see https://developer.mozilla.org/en/docs/Web/API/notification
		 */
		_createWebNotification() {
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
		},
	},
}
</script>

<style lang="scss" scoped>
.notification {
	background-color: var(--color-main-background);

	&::v-deep {
		img.notification-icon {
			display: flex;
			width: 32px;
			height: 32px;
			filter: var(--background-invert-if-dark);
		}
		.rich-text--wrapper {
			white-space: pre-wrap;
			word-break: break-word;
		}
	}

	.notification-subject {
		padding: 4px;
	}

	a.notification-subject:focus-visible {
		box-shadow: inset 0 0 0 2px var(--color-main-text) !important; // override rule in core/css/headers.scss #header a:focus-visible
	}
}

</style>
