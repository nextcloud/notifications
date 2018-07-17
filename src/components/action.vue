<template>
	<button class="action-button pull-right" :class="{ primary: primary }"
		:data-type="type" :data-href="link" @click="onClickActionButton">{{label}}</button>
</template>

<script>
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
				$.ajax({
					url: this.link,
					type: this.type || 'GET',
					success: function () {
						this.$parent._$el.fadeOut(OC.menuSpeed);
						this.$parent.$emit('remove');
						$('body').trigger(new $.Event('OCA.Notification.Action', {
							notification: this.$parent,
							action: {
								url: this.link,
								type: this.type || 'GET'
							}
						}));
					}.bind(this),
					error: function () {
						OC.Notification.showTemporary(t('notifications', 'Failed to perform action'));
					}
				});
			}
		}
	}
</script>
