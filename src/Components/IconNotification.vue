<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<span v-if="showDot || showWarning" class="notifications-button__icon">
		<!-- Modified IconBell from material design icons -->
		<svg
			xmlns="http://www.w3.org/2000/svg"
			xmlns:xlink="http://www.w3.org/1999/xlink"
			version="1.1"
			:width="size"
			:height="size"
			viewBox="0 0 24 24"
			fill="currentColor">
			<path d="M 19,11.79 C 18.5,11.92 18,12 17.5,12 14.47,12 12,9.53 12,6.5 12,5.03 12.58,3.7 13.5,2.71 13.15,2.28 12.61,2 12,2 10.9,2 10,2.9 10,4 V 4.29 C 7.03,5.17 5,7.9 5,11 v 6 l -2,2 v 1 H 21 V 19 L 19,17 V 11.79 M 12,23 c 1.11,0 2,-0.89 2,-2 h -4 c 0,1.11 0.9,2 2,2 z" />
			<path
				class="notification__dot"
				:class="{
					'notification__dot--warning': showWarning && !isOrangeThemed,
					'notification__dot--white': isRedThemed || (showWarning && isOrangeThemed),
				}"
				d="M 21,6.5 C 21,8.43 19.43,10 17.5,10 15.57,10 14,8.43 14,6.5 14,4.57 15.57,3 17.5,3 19.43,3 21,4.57 21,6.5" />
		</svg>
	</span>
	<IconBell v-else class="notifications-button__icon" :size="size" />
</template>

<script setup>
import { getCapabilities } from '@nextcloud/capabilities'
import { computed } from 'vue'
import IconBell from 'vue-material-design-icons/Bell.vue' // Filled icon as it represents app itself

defineProps({
	showDot: {
		type: Boolean,
		default: false,
	},
	showWarning: {
		type: Boolean,
		default: false,
	},
	size: {
		type: Number,
		default: 20,
	},
})

const theming = getCapabilities()?.theming

const hexRegex = /^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/

/**
 * @param {string} hexColor color in HEX format, like #00679e
 */
function hexToHSL(hexColor) {
	const r = parseInt(hexColor.substring(1, 3), 16) / 255
	const g = parseInt(hexColor.substring(3, 5), 16) / 255
	const b = parseInt(hexColor.substring(5, 7), 16) / 255

	const max = Math.max(r, g, b)
	const min = Math.min(r, g, b)
	let hue = 0
	let sat = 0
	const lum = (max + min) / 2

	if (max !== min) {
		const d = max - min
		sat = lum > 0.5 ? d / (2 - max - min) : d / (max + min)
		switch (max) {
			case r:
				hue = (g - b) / d + (g < b ? 6 : 0)
				break
			case g:
				hue = (b - r) / d + 2
				break
			case b:
				hue = (r - g) / d + 4
				break
		}
		hue *= 60
	}

	return [hue, sat, lum]
}

const isRedThemed = computed(() => {
	if (!theming?.color || !hexRegex.test(theming?.color)) {
		return false
	}
	const [hue, sat, lum] = hexToHSL(theming.color)
	return (hue >= 330 || hue <= 15) && sat > 0.4 && (lum > 0.1 || lum < 0.6)
})

const isOrangeThemed = computed(() => {
	if (!theming?.color || !hexRegex.test(theming?.color)) {
		return false
	}
	const [hue, sat, lum] = hexToHSL(theming.color)
	return (hue >= 305 || hue <= 64) && sat > 0.7 && (lum > 0.1 || lum < 0.6)
})
</script>
