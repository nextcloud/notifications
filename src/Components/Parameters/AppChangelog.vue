<!--
  - @copyright Copyright (c) 2024 Ferdinand Thiessen <opensource@fthiessen.de>
  -
  - @author Ferdinand Thiessen <opensource@fthiessen.de>
  -
  - @license AGPL-3.0-or-later
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
-->

<template>
	<NcButton v-if="canShowChangelog" class="app-changelog-button" @click="handleClick">
		{{ t('notifications', 'See what\'s new for {app}', { app: name }) }}
	</NcButton>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'

export default {
	name: 'AppChangelog',

	components: {
		NcButton,
	},

	props: {
		type: {
			type: String,
			required: true,
		},
		id: {
			type: String,
			required: true,
		},
		name: {
			type: String,
			required: true,
		},
		dismissNotification: {
			type: Function,
			required: false,
			default: () => {},
		}
	},

	setup() {
		// Non reactive props
		return {
			canShowChangelog: window.OCA?.UpdateNotification?.showAppChangelogDialog !== undefined,
		}
	},

	methods: {
		async handleClick() {
			if (await window.OCA.UpdateNotification.showAppChangelogDialog(this.id)) {
				this.dismissNotification()
			}
		},
	},
}
</script>

<style scoped>
.app-changelog-button {
	display: block;
	margin-top: 12px;
}
</style>