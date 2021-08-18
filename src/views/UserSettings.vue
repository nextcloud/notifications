<!--
  - @copyright Copyright (c) 2021 Julien Barnoin <julien@barnoin.com>
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
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
	<div>
		<SettingsSection :title="t('notifications', 'Notifications')">
			<div v-if="config.email_enabled" class="notification-frequency">
				<div class="notification-frequency__warning">
					<strong v-if="!config.is_email_set">{{ t('notifications', 'You need to set up your email address before you can receive notification emails.') }}</strong>
				</div>
				<p>
					<input id="notifications_email_enabled"
						v-model="config.notifications_email_enabled"
						type="checkbox"
						class="checkbox"
						@change="updateSettings()">
					<label for="notifications_email_enabled">
						{{ t('notifications', 'Send unread notifications as email') }}
					</label>
				</p>
				<p>
					<label for="notify_setting_batchtime" class="notification-frequency__label">{{ t('notifications', 'Send notification emails:') }}</label>
					<select
						v-model="config.setting_batchtime"
						class="notification-frequency__select"
						name="notify_setting_batchtime"
						@change="updateSettings()">
						<option v-for="option in batchtime_options" :key="option.value" :value="option.value">
							{{ option.text }}
						</option>
					</select>
				</p>
			</div>
		</SettingsSection>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import { showSuccess, showError } from '@nextcloud/dialogs'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'

const EmailFrequency = {
	EMAIL_SEND_HOURLY: 0,
	EMAIL_SEND_DAILY: 1,
	EMAIL_SEND_WEEKLY: 2,
	EMAIL_SEND_ASAP: 3,
}

export default {
	name: 'UserSettings',
	components: {
		SettingsSection,
	},

	data() {
		return {
			batchtime_options: [
				{ text: t('notifications', 'As soon as possible'), value: EmailFrequency.EMAIL_SEND_ASAP },
				{ text: t('notifications', 'Hourly'), value: EmailFrequency.EMAIL_SEND_HOURLY },
				{ text: t('notifications', 'Daily'), value: EmailFrequency.EMAIL_SEND_DAILY },
				{ text: t('notifications', 'Weekly'), value: EmailFrequency.EMAIL_SEND_WEEKLY },
			],
			config: loadState('notifications', 'config'),
		}
	},

	methods: {
		async updateSettings() {
			try {
				const form = new FormData()
				form.append('notifications_email_enabled', this.config.notifications_email_enabled ? '1' : '0')
				form.append('notify_setting_batchtime', this.config.setting_batchtime)
				const response = await axios.post(generateOcsUrl('apps/notifications/api/v2/settings'), form)
				showSuccess(response.data.ocs.data.message)
			} catch (error) {
				showError(t('notifications', 'Error updating settings'))
			}
		},
	},
}

</script>
