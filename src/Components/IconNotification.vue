<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<IconBell v-if="!showDot && !showWarning"
		class="notifications-button__icon"
		:size="20"
		:title="t('notifications', 'Notifications')" />
	<!-- From material design icons -->
	<svg v-else
		class="notifications-button__icon"
		xmlns="http://www.w3.org/2000/svg"
		xmlns:xlink="http://www.w3.org/1999/xlink"
		version="1.1"
		width="20"
		height="20"
		viewBox="0 0 24 24"
		fill="currentColor">
		<path d="M 19,11.79 C 18.5,11.92 18,12 17.5,12 14.47,12 12,9.53 12,6.5 12,5.03 12.58,3.7 13.5,2.71 13.15,2.28 12.61,2 12,2 10.9,2 10,2.9 10,4 V 4.29 C 7.03,5.17 5,7.9 5,11 v 6 l -2,2 v 1 H 21 V 19 L 19,17 V 11.79 M 12,23 c 1.11,0 2,-0.89 2,-2 h -4 c 0,1.11 0.9,2 2,2 z" />
		<path class="notification__dot"
			:class="{
				'notification__dot--warning': showWarning && !isOrangeThemed,
				'notification__dot--white': isRedThemed || (showWarning && isOrangeThemed),
			}"
			d="M 21,6.5 C 21,8.43 19.43,10 17.5,10 15.57,10 14,8.43 14,6.5 14,4.57 15.57,3 17.5,3 19.43,3 21,4.57 21,6.5" />
	</svg>
</template>

<script>
import IconBell from 'vue-material-design-icons/Bell.vue'

import { getCapabilities } from '@nextcloud/capabilities'

export default {
	name: 'IconNotification',

	components: {
		IconBell,
	},

	props: {
		showDot: {
			type: Boolean,
			default: false,
		},
		showWarning: {
			type: Boolean,
			default: false,
		},
	},

	data() {
		return {
			theming: getCapabilities()?.theming || {},
		}
	},

	computed: {
		isRedThemed() {
			if (this.theming?.color) {
				const hsl = this.rgbToHsl(this.theming.color.substring(1, 3),
					this.theming.color.substring(3, 5),
					this.theming.color.substring(5, 7))
				const h = hsl[0] * 360
				return (h >= 330 || h <= 15) && hsl[1] > 0.4 && (hsl[2] > 0.1 || hsl[2] < 0.6)
			}
			return false
		},
		isOrangeThemed() {
			if (this.theming?.color) {
				const hsl = this.rgbToHsl(this.theming.color.substring(1, 3),
					this.theming.color.substring(3, 5),
					this.theming.color.substring(5, 7))
				const h = hsl[0] * 360
				return (h >= 305 || h <= 64) && hsl[1] > 0.7 && (hsl[2] > 0.1 || hsl[2] < 0.6)
			}
			return false
		},
	},

	methods: {
		rgbToHsl(r, g, b) {
			r = parseInt(r, 16) / 255; g = parseInt(g, 16) / 255; b = parseInt(b, 16) / 255
			const max = Math.max(r, g, b); const min = Math.min(r, g, b)
			let h; let s; const l = (max + min) / 2

			if (max === min) {
				h = s = 0
			} else {
				const d = max - min
				s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
				switch (max) {
				case r: h = (g - b) / d + (g < b ? 6 : 0); break
				case g: h = (b - r) / d + 2; break
				case b: h = (r - g) / d + 4; break
				}
				h /= 6
			}

			return [h, s, l]
		},
	},
}
</script>
