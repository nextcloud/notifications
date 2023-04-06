<template>
	<li class="notification"
		:data-id="notificationId"
		:data-timestamp="timestamp"
		:data-object-type="objectType"
		:data-app="app">
		<div class="notification-heading">
			<span class="hidden-visually">{{ absoluteDate }}</span>
			<span v-if="timestamp"
				:title="absoluteDate"
				class="notification-time live-relative-timestamp"
				:data-timestamp="timestamp">{{ relativeDate }}</span>
			<NcButton v-if="timestamp"
				class="notification-dismiss-button"
				type="tertiary"
				:aria-label="t('notifications', 'Dismiss')"
				@click="onDismissNotification">
				<template #icon>
					<Close :size="20" />
				</template>
			</NcButton>
		</div>

		<a v-if="externalLink"
			:href="externalLink"
			class="notification-subject full-subject-link external"
			target="_blank"
			rel="noreferrer noopener">
			<span class="image"><img :src="icon" class="notification-icon" alt=""></span>
			<span class="subject">{{ subject }} ↗</span>
		</a>
		<a v-else-if="useLink" :href="link" class="notification-subject full-subject-link">
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
		<div v-else-if="externalLink" class="notification-actions">
			<NcButton type="primary"
				href="https://nextcloud.com/pushnotifications"
				class="action-button pull-right"
				target="_blank"
				rel="noreferrer noopener">
				<template #icon>
					<Message :size="20" />
				</template>
				{{ t('notifications', 'Contact Nextcloud GmbH') }} ↗
			</NcButton>
		</div>
	</li>
</template>

<script>
import axios from '@nextcloud/axios'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import Close from 'vue-material-design-icons/Close.vue'
import Message from 'vue-material-design-icons/Message.vue'
import { showError } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { Howl } from 'howler'
import Action from './Action.vue'
import { generateOcsUrl, generateFilePath } from '@nextcloud/router'
import moment from '@nextcloud/moment'
import RichText from '@nextcloud/vue-richtext'
import DefaultParameter from './Parameters/DefaultParameter.vue'
import File from './Parameters/File.vue'
import User from './Parameters/User.vue'
import { emit } from '@nextcloud/event-bus'

export default {
	name: 'Notification',

	components: {
		Action,
		NcButton,
		Close,
		Message,
		RichText,
	},

	props: {
		notificationId: {
			type: Number,
			default: -1,
		},
		datetime: {
			type: String,
			default: '',
		},
		app: {
			type: String,
			default: '',
		},
		icon: {
			type: String,
			default: '',
		},
		link: {
			type: String,
			default: '',
		},
		externalLink: {
			type: String,
			default: '',
		},
		user: {
			type: String,
			default: '',
		},
		message: {
			type: String,
			default: '',
		},
		messageRich: {
			type: String,
			default: '',
		},
		messageRichParameters: {
			type: [Object, Array],
			default() {
				return {}
			},
		},
		subject: {
			type: String,
			default: '',
		},
		subjectRich: {
			type: String,
			default: '',
		},
		subjectRichParameters: {
			type: [Object, Array],
			default() {
				return {}
			},
		},
		objectType: {
			type: String,
			default: '',
		},
		objectId: {
			type: String,
			default: '',
		},
		shouldNotify: {
			type: Boolean,
			default: true,
		},
		actions: {
			type: Array,
			default() {
				return []
			},
		},

		index: {
			type: Number,
			default: -1,
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
			if (this.datetime === 'warning') {
				return 0
			}
			return (new Date(this.datetime)).valueOf()
		},
		absoluteDate() {
			if (this.datetime === 'warning') {
				return ''
			}
			return moment(this.timestamp).format('LLL')
		},
		relativeDate() {
			if (this.datetime === 'warning') {
				return ''
			}

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
				if (this.subjectRichParameters[p].link) {
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

		if (this.$parent.$parent.$parent.backgroundFetching) {
			// Can not rely on showBrowserNotifications because each tab should
			// be able to utilize the data from the notification in events.
			const event = {
				notification: this.$props,
			}

			emit('notifications:notification:received', event)
		}

		if (this.shouldNotify && this.$parent.$parent.$parent.showBrowserNotifications) {
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
			if (e.target.closest('.rich-text--wrapper')) {
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
				n.onclick = async function(e) {
					const event = {
						cancelAction: false,
						notification: this.$props,
						action: {
							url: this.link,
							type: 'WEB',
						},
					}
					await emit('notifications:action:execute', event)

					if (!event.cancelAction) {
						console.debug('Redirecting because of a click onto a notification', this.link)
						window.location.href = this.link
					}

					// Best effort try to bring the tab to the foreground (works at least in Chrome, not in Firefox)
					window.focus()
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
