<template>
	<div class="notification" :data-id="notificationId" :data-timestamp="timestamp">
		<div class="notification-heading">
			<span class="notification-time has-tooltip live-relative-timestamp" :data-timestamp="timestamp" :title="absoluteDate">{{ relativeDate }}</span>
			<div class="notification-delete" @click="onDismissNotification">
				<span class="icon icon-close svg" :title="t('notifications', 'Dismiss')" />
			</div>
		</div>
		<a v-if="useLink" :href="link" class="notification-subject full-subject-link">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon"></span>
			<span class="text" v-html="renderedSubject" />
		</a>
		<div v-else class="notification-subject">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon"></span>
			<span class="text" v-html="renderedSubject" />
		</div>
		<div v-if="message" class="notification-message" @click="onClickMessage">
			<div class="message-container" :class="{ collapsed: isCollapsedMessage }" v-html="renderedMessage" />
			<div v-if="isCollapsedMessage" class="notification-overflow" />
		</div>
		<div v-if="actions.length" class="notification-actions">
			<action v-for="(a, i) in actions" :key="i" v-bind="a" />
		</div>
	</div>
</template>

<script>
import axios from 'nextcloud-axios'
import Action from './Action'
import parser from '../richObjectStringParser'
import escapeHTML from 'escape-html'

export default {
	name: 'Notification',

	components: {
		Action
	},

	props: {
		notificationId: {
			type: Number,
			default: -1,
			required: true
		},
		datetime: {
			type: String,
			default: '',
			required: true
		},
		app: {
			type: String,
			default: '',
			required: true
		},
		icon: {
			type: String,
			default: '',
			required: true
		},
		link: {
			type: String,
			default: '',
			required: true
		},
		user: {
			type: String,
			default: '',
			required: true
		},
		message: {
			type: String,
			default: '',
			required: true
		},
		messageRich: {
			type: String,
			default: '',
			required: true
		},
		messageRichParameters: {
			type: [Object, Array],
			default() {
				return {}
			},
			required: true
		},
		subject: {
			type: String,
			default: '',
			required: true
		},
		subjectRich: {
			type: String,
			default: '',
			required: true
		},
		subjectRichParameters: {
			type: [Object, Array],
			default() {
				return {}
			},
			required: true
		},
		objectType: {
			type: String,
			default: '',
			required: true
		},
		objectId: {
			type: String,
			default: '',
			required: true
		},
		actions: {
			type: Array,
			default() {
				return []
			},
			required: true
		},

		index: {
			type: Number,
			default: -1,
			required: true
		}
	},

	data() {
		return {
			showFullMessage: {
				type: Boolean,
				default: false
			}
		}
	},

	_$el: null,

	computed: {
		timestamp: function() {
			return moment(this.datetime).format('X') * 1000
		},
		absoluteDate: function() {
			return OC.Util.formatDate(this.timestamp)
		},
		relativeDate: function() {
			return OC.Util.relativeModifiedDate(this.timestamp)
		},
		useLink: function() {
			return this.link && this.renderedSubject.indexOf('<a ') === -1
		},
		renderedSubject: function() {
			if (this.subjectRich.length !== 0) {
				return parser.parseMessage(
					this.subjectRich.replace(new RegExp('\n', 'g'), ' '),
					this.subjectRichParameters
				)
			}

			return escapeHTML(this.subject).replace(new RegExp('\n', 'g'), ' ')
		},
		isCollapsedMessage: function() {
			return this.message.length > 200 && !this.showFullMessage
		},
		renderedMessage: function() {
			if (this.messageRich.length !== 0) {
				return parser.parseMessage(
					this.messageRich,
					this.messageRichParameters
				)
			}

			return escapeHTML(this.message).replace(new RegExp('\n', 'g'), '<br>')
		}
	},

	mounted: function() {
		this._$el = $(this.$el)

		this._$el.find('.avatar').each(function() {
			var element = $(this)
			if (element.data('user-display-name')) {
				element.avatar(element.data('user'), 21, undefined, false, undefined, element.data('user-display-name'))
			} else {
				element.avatar(element.data('user'), 21)
			}
		})

		this._$el.find('.avatar-name-wrapper').each(function() {
			var element = $(this)
			var avatar = element.find('.avatar')
			var label = element.find('strong')

			$.merge(avatar, label).contactsMenu(element.data('user'), 0, element)
		})

		this._$el.find('.has-tooltip').tooltip({
			// container: this.$container.find('.notification-wrapper'),
			placement: 'bottom'
		})

		// Parents: TransitionGroup > NotificationsList
		if (this.$parent.$parent.backgroundFetching) {
			this._triggerWebNotification()
		}
	},

	methods: {
		onClickMessage: function(e) {
			if (e.target.classList.contains('message-container')) {
				this.showFullMessage = !this.showFullMessage
			}
		},

		onDismissNotification: function() {
			axios
				.delete(OC.linkToOCS('apps/notifications/api/v2', 2) + 'notifications/' + this.notificationId)
				.then(() => {
					// this._$el.fadeOut(OC.menuSpeed)
					this.$emit('remove', this.index)
				})
				.catch(() => {
					OC.Notification.showTemporary(t('notifications', 'Failed to dismiss notification'))
				})
		},

		/**
			 * Check if we do web notifications
			 */
		_triggerWebNotification: function() {
			// Trigger browsers web notification
			if ('Notification' in window) {
				if (Notification.permission === 'granted') {
					// If it's okay let's create a notification
					this._createWebNotification()

				// Otherwise, we need to ask the user for permission
				} else if (Notification.permission !== 'denied') {
					Notification.requestPermission(function(permission) {
						// If the user accepts, let's create a notification
						if (permission === 'granted') {
							this._createWebNotification()
						}
					}.bind(this))
				}
			}
		},

		/**
			 * Create a browser notification
			 * @see https://developer.mozilla.org/en/docs/Web/API/notification
			 */
		_createWebNotification: function() {
			var n = new Notification(this.subject, {
				title: this.subject,
				lang: OC.getLocale(),
				body: this.message,
				icon: this.icon,
				tag: this.notificationId
			})

			if (this.link) {
				n.onclick = function(event) {
					event.preventDefault()
					window.location.href = this.link
				}.bind(this)
			}

			setTimeout(n.close.bind(n), 5000)
		}
	}
}
</script>
