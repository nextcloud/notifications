<!--
  - @copyright Copyright (c) 2019 Joas Schilling <coding@schilljs.com>
  -
  - @author Joas Schilling <coding@schilljs.com>
  -
  - @license GNU AGPL version 3 or any later version
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
	<div class="mention">
		<NcUserBubble v-if="!cloudId"
			:display-name="name"
			:user="id" />
		<strong v-else
			:title="cloudId">
			{{ name }}
		</strong>
	</div>
</template>

<script>

import NcUserBubble from '@nextcloud/vue/dist/Components/NcUserBubble.js'

export default {
	name: 'User',

	components: {
		NcUserBubble,
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
		server: {
			type: String,
			default: '',
		},
	},

	computed: {
		cloudId() {
			return this.server ? (this.id + '@' + this.server) : ''
		},
	},
}
</script>

<style lang="scss" scoped>
.mention {
	display: contents;
	white-space: nowrap;
}
</style>
