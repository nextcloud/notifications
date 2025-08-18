<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<li
		class="notification"
		:data-id="notification.notificationId"
		:data-timestamp="timestamp"
		:data-object-type="notification.objectType"
		:data-app="notification.app">
		<div class="notification-heading">
			<NcDateTime
				v-if="timestamp"
				class="notification-time"
				ignore-seconds
				:format="{ timeStyle: 'short', dateStyle: 'long' }"
				:timestamp="timestamp" />
			<NcButton
				v-if="timestamp"
				class="notification-dismiss-button"
				variant="tertiary"
				:aria-label="t('notifications', 'Dismiss')"
				@click="onDismissNotification">
				<template #icon>
					<IconClose :size="20" />
				</template>
			</NcButton>
		</div>

		<a
			v-if="notification.externalLink"
			:href="notification.externalLink"
			class="notification-subject full-subject-link external"
			target="_blank"
			rel="noreferrer noopener">
			<span v-if="notification.icon" class="image"><img :src="notification.icon" class="notification-icon" alt=""></span>
			<span class="subject">{{ notification.subject }} ↗</span>
		</a>
		<a v-else-if="useLink" :href="notification.link" class="notification-subject full-subject-link">
			<span v-if="notification.icon" class="image"><img :src="notification.icon" class="notification-icon" alt=""></span>
			<NcRichText
				v-if="notification.subjectRich"
				:text="notification.subjectRich"
				:arguments="preparedSubjectParameters" />
			<span v-else class="subject">{{ notification.subject }}</span>
		</a>
		<div v-else class="notification-subject">
			<span v-if="notification.icon" class="image"><img :src="notification.icon" class="notification-icon" alt=""></span>
			<NcRichText
				v-if="notification.subjectRich"
				:text="notification.subjectRich"
				:arguments="preparedSubjectParameters" />
			<span v-else class="subject">{{ notification.subject }}</span>
		</div>

		<div v-if="notification.message" class="notification-message" @click="onClickMessage">
			<div class="message-container" :class="{ collapsed: isCollapsedMessage }">
				<NcRichText
					v-if="notification.messageRich"
					:text="notification.messageRich"
					:arguments="preparedMessageParameters"
					:autolink="true" />
				<span v-else>{{ notification.message }}</span>
			</div>
			<div v-if="isCollapsedMessage" class="notification-overflow" />
		</div>

		<div v-if="notification.actions.length" class="notification-actions">
			<ActionButton
				v-for="(action, i) in notification.actions"
				:key="i"
				:action="action"
				@click="onClickAction"
				@remove="$emit('remove')" />
		</div>
		<div v-else-if="notification.externalLink" class="notification-actions">
			<NcButton
				variant="primary"
				href="https://nextcloud.com/fairusepolicy"
				class="action-button pull-right"
				target="_blank"
				rel="noreferrer noopener">
				<template #icon>
					<IconMessageOutline :size="20" />
				</template>
				{{ t('notifications', 'Contact Nextcloud GmbH') }} ↗
			</NcButton>
		</div>
	</li>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'
import { emit } from '@nextcloud/event-bus'
import { t } from '@nextcloud/l10n'
import { generateOcsUrl } from '@nextcloud/router'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDateTime from '@nextcloud/vue/components/NcDateTime'
import NcRichText from '@nextcloud/vue/components/NcRichText'
import IconClose from 'vue-material-design-icons/Close.vue'
import IconMessageOutline from 'vue-material-design-icons/MessageOutline.vue'
import ActionButton from './ActionButton.vue'
import DefaultParameter from './Parameters/DefaultParameter.vue'
import FileParameter from './Parameters/FileParameter.vue'
import UserParameter from './Parameters/UserParameter.vue'

/**
 * @typedef {object} NotificationItem
 * @property {number} notification_id notification id (required)
 * @property {string} app app id (required)
 * @property {string} user user id (required)
 * @property {string} datetime timestamp of notification (required)
 * @property {string} object_type object type, e.g. 'room' (required)
 * @property {string} object_id object id, e.g. room token (required)
 * @property {string} subject notification subject (required)
 * @property {string} message notification message (required)
 * @property {string} link notification link (required)
 * @property {NotificationAction[]} actions notification list of actions (required)
 * @property {string} [subjectRich] notification subject with rich parameters
 * @property {object} [subjectRichParameters] rich parameters for notification subject
 * @property {string} [messageRich] notification message with rich parameters
 * @property {object} [messageRichParameters] rich parameters for notification message
 * @property {string} [icon] icon to render
 * @property {boolean} [shouldNotify] whether a browser notification should be rendered
 */

/**
 * @typedef {object} NotificationAction
 * @property {string} label action label (required)
 * @property {string} link action link (required)
 * @property {string} type action type (required)
 * @property {boolean} primary action primary (required)
 */

export default {
	name: 'NotificationItem',

	components: {
		ActionButton,
		NcButton,
		NcDateTime,
		IconClose,
		IconMessageOutline,
		NcRichText,
	},

	props: {
		notification: {
			/** @type {import('vue').PropType<NotificationItem>} */
			type: Object,
			required: true,
		},
	},

	emits: ['remove'],

	data() {
		return {
			showFullMessage: false,
		}
	},

	computed: {
		timestamp() {
			if (this.notification.datetime === 'warning') {
				return 0
			}
			return (new Date(this.notification.datetime)).valueOf()
		},

		useLink() {
			if (!this.notification.link) {
				return false
			}

			let parametersHaveLink = false
			Object.keys(Object(this.notification.subjectRichParameters)).forEach((p) => {
				if (Object(this.notification.subjectRichParameters)[p].link) {
					parametersHaveLink = true
				}
			})
			return !parametersHaveLink
		},

		preparedSubjectParameters() {
			return this.prepareParameters(this.notification.subjectRichParameters)
		},

		preparedMessageParameters() {
			return this.prepareParameters(this.notification.messageRichParameters)
		},

		isCollapsedMessage() {
			return this.notification.message.length > 200 && !this.showFullMessage
		},
	},

	methods: {
		t,

		prepareParameters(parameters = {}) {
			const richParameters = {}
			Object.keys(parameters).forEach((p) => {
				const type = parameters[p].type
				if (type === 'user') {
					richParameters[p] = {
						component: UserParameter,
						props: parameters[p],
					}
				} else if (type === 'file') {
					richParameters[p] = {
						component: FileParameter,
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
			} else if (!this.notification.messageRich && !!this.notification.message) {
				// Plain text
				this.showFullMessage = !this.showFullMessage
			}
		},

		async onClickAction({ event, action }) {
			try {
				const executeEvent = {
					cancelAction: false,
					notification: this.notification,
					action,
				}
				await emit('notifications:action:execute', executeEvent)

				if (action.type === 'WEB') {
					if (executeEvent.cancelAction) {
						event.preventDefault()
					}
					return
				}

				if (executeEvent.cancelAction) {
					return
				}

				// execute action
				await axios({
					method: action.type,
					url: action.url,
				})

				// emit event to current app
				this.$emit('remove')

				emit('notifications:action:executed', event)
			} catch (error) {
				console.error('Failed to perform action', error)
				showError(t('notifications', 'Failed to perform action'))
			}
		},

		onDismissNotification() {
			axios
				.delete(generateOcsUrl('apps/notifications/api/v2/notifications/{id}', { id: this.notification.notificationId }))
				.then(() => {
					this.$emit('remove')
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
		overflow-wrap: break-word;
	}

	.notification-subject {
		padding: 4px;
	}

	a.notification-subject:focus-visible {
		box-shadow: inset 0 0 0 2px var(--color-main-text) !important; // override rule in core/css/headers.scss #header a:focus-visible
	}
}

</style>
