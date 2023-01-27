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
	<a :title="title"
		class="filename"
		:href="link">{{ name }}</a>
</template>

<script>

export default {
	name: 'File',

	props: {
		type: {
			type: String,
			required: true,
		},
		id: {
			type: [Number, String],
			required: true,
		},
		name: {
			type: String,
			required: true,
		},
		path: {
			type: String,
			default: '',
		},
		link: {
			type: String,
			default: '',
		},
	},

	computed: {
		title() {
			const lastSlashPosition = this.path.lastIndexOf('/')
			const firstSlashPosition = this.path.indexOf('/')
			const path = this.path.substring(firstSlashPosition === 0 ? 1 : 0, lastSlashPosition)

			if (path.length === 0) {
				return ''
			}

			return t('notifications', 'in {path}', {
				path,
			})
		},
	},
}
</script>
