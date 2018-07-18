<template>
	<button class="action-button pull-right" :class="{ primary: primary }"
		:data-type="type" :data-href="link" @click="onClickActionButton">{{label}}</button>
</template>

<script>
	import axios from 'axios';
	export default {
		name: 'action',

		props: [
			'label',
			'link',
			'type',
			'primary'
		],

		methods: {
			onClickActionButton: function () {
				axios({
					method: this.type || 'GET',
					url: this.link,
					headers: { requesttoken: OC.requestToken }
				})
				.then(response => {
					this.$parent._$el.fadeOut(OC.menuSpeed);
					this.$parent.$emit('remove');
					$('body').trigger(new $.Event('OCA.Notification.Action', {
						notification: this.$parent,
						action: {
							url: this.link,
							type: this.type || 'GET'
						}
					}));
				})
				.catch(err => {
					OC.Notification.showTemporary(t('notifications', 'Failed to perform action'));
				});
			}
		}
	}
</script>
