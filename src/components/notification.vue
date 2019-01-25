<template>
	<div class="notification" :data-id="notification_id" :data-timestamp="timestamp">
		<div class="notification-heading">
			<span class="notification-time has-tooltip live-relative-timestamp" :data-timestamp="timestamp" :title="absoluteDate">{{relativeDate}}</span>
			<div class="notification-delete" @click="onDismissNotification">
				<span class="icon icon-close svg" :title="t('notifications', 'Dismiss')"></span>
			</div>
		</div>
		<a v-if="useLink" :href="link" class="notification-subject full-subject-link">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon"></span>
			<span class="text" v-html="renderedSubject"></span>
		</a>
		<div v-else class="notification-subject">
			<span v-if="icon" class="image"><img :src="icon" class="notification-icon"></span>
			<span class="text" v-html="renderedSubject"></span>
		</div>
		<div class="notification-message" v-if="message" @click="onClickMessage">
			<div class="message-container" :class="{ collapsed: isCollapsedMessage }" v-html="renderedMessage"></div>
			<div v-if="isCollapsedMessage" class="notification-overflow"></div>
		</div>
		<div class="notification-actions" v-if="actions.length">
			<action v-for="(a, index) in actions" v-bind="a" :key="index"></action>
		</div>
	</div>
</template>

<script>
	import axios from 'nextcloud-axios';
	import action from './action';
	import parser from '../richObjectStringParser';

	export default {
		name: 'notification',

		props: [
			'notification_id',
			'datetime',
			'app',
			'icon',
			'link',
			'user',
			'message',
			'messageRich',
			'messageRichParameters',
			'subject',
			'subjectRich',
			'subjectRichParameters',
			'object_type',
			'object_id',
			'actions',
		],

		data: function() {
			return {
				showFullMessage: false,
			}
		},

		_$el: null,

		computed: {
			timestamp: function() {
				return moment(this.datetime).format('X') * 1000;
			},
			absoluteDate: function() {
				return OC.Util.formatDate(this.timestamp);
			},
			relativeDate: function() {
				return OC.Util.relativeModifiedDate(this.timestamp);
			},
			useLink: function() {
				return this.link && this.renderedSubject.indexOf('<a ') === -1;
			},
			renderedSubject: function() {
				if (this.subjectRich.length !== 0) {
					return parser.parseMessage(
						this.subjectRich.replace(new RegExp("\n", 'g'), ' '),
						this.subjectRichParameters
					);
				}

				return escapeHTML(this.subject).replace(new RegExp("\n", 'g'), ' ');
			},
			isCollapsedMessage: function() {
				return this.message.length > 200 && !this.showFullMessage;
			},
			renderedMessage: function() {
				if (this.messageRich.length !== 0) {
					return parser.parseMessage(
						this.messageRich,
						this.messageRichParameters
					);
				}

				return escapeHTML(this.message).replace(new RegExp("\n", 'g'), '<br>');
			}
		},

		methods: {
			onClickMessage: function(e) {
				if (e.target.classList.contains('message-container')) {
					this.showFullMessage = !this.showFullMessage;
				}
			},

			onDismissNotification: function() {
				axios
					.delete(OC.linkToOCS('apps/notifications/api/v2', 2) + 'notifications/' + this.notification_id)
					.then(response => {
						this._$el.fadeOut(OC.menuSpeed);
						this.$emit('remove');
					})
					.catch(err => {
						OC.Notification.showTemporary(t('notifications', 'Failed to dismiss notification'));
					});
			},

			/**
			 * Check if we do web notifications
			 */
			_triggerWebNotification: function () {
				// Trigger browsers web notification
				if ("Notification" in window) {
					if (Notification.permission === "granted") {
						// If it's okay let's create a notification
						this._createWebNotification();
					}

					// Otherwise, we need to ask the user for permission
					else if (Notification.permission !== 'denied') {
						Notification.requestPermission(function (permission) {
							// If the user accepts, let's create a notification
							if (permission === "granted") {
								this._createWebNotification();
							}
						}.bind(this));
					}
				}
			},

			/**
			 * Create a browser notification
			 * @see https://developer.mozilla.org/en/docs/Web/API/notification
			 */
			_createWebNotification: function () {
				var n = new Notification(this.subject, {
					title: this.subject,
					lang: OC.getLocale(),
					body: this.message,
					icon: this.icon,
					tag: this.notification_id
				});

				if (this.link) {
					n.onclick = function(event) {
						event.preventDefault();
						window.location.href = this.link;
					}.bind(this);
				}

				setTimeout(n.close.bind(n), 5000);
			}
		},

		mounted: function () {
			this._$el = $(this.$el);

			this._$el.find('.avatar').each(function() {
				var element = $(this);
				if (element.data('user-display-name')) {
					element.avatar(element.data('user'), 21, undefined, false, undefined, element.data('user-display-name'));
				} else {
					element.avatar(element.data('user'), 21);
				}
			});

			this._$el.find('.avatar-name-wrapper').each(function() {
				var element = $(this);
				var avatar = element.find('.avatar');
				var label = element.find('strong');

				$.merge(avatar, label).contactsMenu(element.data('user'), 0, element);
			});

			this._$el.find('.has-tooltip').tooltip({
				//container: this.$container.find('.notification-wrapper'),
				placement: 'bottom'
			});

			if (this.$parent.backgroundFetching) {
				this._triggerWebNotification();
			}
		},

		components: {
			action
		}
	}
</script>
