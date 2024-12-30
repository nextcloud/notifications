<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
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
			<NcRichText v-if="subjectRich"
				:text="subjectRich"
				:arguments="preparedSubjectParameters" />
			<span v-else class="subject">{{ subject }}</span>
		</a>
		<div v-else class="notification-subject">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon" alt=""></span>
			<NcRichText v-if="subjectRich"
				:text="subjectRich"
				:arguments="preparedSubjectParameters" />
			<span v-else class="subject">{{ subject }}</span>
		</div>

		<div v-if="message" class="notification-message" @click="onClickMessage">
			<div class="message-container" :class="{ collapsed: isCollapsedMessage }">
				<NcRichText v-if="messageRich"
					:text="messageRich"
					:arguments="preparedMessageParameters"
					:autolink="true" />
				<span v-else>{{ message }}</span>
			</div>
			<div v-if="isCollapsedMessage" class="notification-overflow" />
		</div>

		<div v-if="actions.length" class="notification-actions">
			<Action v-for="(a, i) in actions"
				:key="i"
				v-bind="a"
				:notification-index="index" />
		</div>
		<div v-else-if="externalLink" class="notification-actions">
			<NcButton type="primary"
				href="https://nextcloud.com/fairusepolicy"
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
import NcRichText from '@nextcloud/vue/dist/Components/NcRichText.js'
import Close from 'vue-material-design-icons/Close.vue'
import Message from 'vue-material-design-icons/Message.vue'
import { showError } from '@nextcloud/dialogs'
import Action from './Action.vue'
import { generateOcsUrl } from '@nextcloud/router'
import moment from '@nextcloud/moment'
import DefaultParameter from './Parameters/DefaultParameter.vue'
import File from './Parameters/File.vue'
import User from './Parameters/User.vue'

export default {
	name: 'Notification',

	components: {
		Action,
		NcButton,
		Close,
		Message,
		NcRichText,
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
	},
}
</script>

<style lang="scss" scoped>
.notification {
	:deep(img.notification-icon) {
		display: flex;
		width: 32px;
		height: 32px;
		filter: var(--background-invert-if-dark);
	}
	:deep(.rich-text--wrapper) {
		white-space: pre-wrap;
		word-break: break-word;
	}

	.notification-subject {
		padding: 4px;
	}

	a.notification-subject:focus-visible {
		box-shadow: inset 0 0 0 2px var(--color-main-text) !important; // override rule in core/css/headers.scss #header a:focus-visible
	}
}

</style>
