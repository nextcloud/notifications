<!--
  - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcSettingsSection :name="t('notifications', 'Notifications defaults')"
		:description="t('notifications', 'Configure the default notification settings for new users')">
		<p>
			<label for="notify_setting_batchtime" class="notification-frequency__label">
				{{ t('notifications', 'Send email reminders about unhandled notifications after:') }}
			</label>
			<select id="notify_setting_batchtime"
				v-model="config.setting_batchtime"
				class="notification-frequency__select"
				@change="updateSettings()">
				<option v-for="option in batchtime_options" :key="option.value" :value="option.value">
					{{ option.text }}
				</option>
			</select>
		</p>

		<NcCheckboxRadioSwitch :checked.sync="config.sound_notification"
			@update:checked="updateSettings">
			{{ t('notifications', 'Play sound when a new notification arrives') }}
		</NcCheckboxRadioSwitch>
		<NcCheckboxRadioSwitch :checked.sync="config.sound_talk"
			@update:checked="updateSettings">
			{{ t('notifications', 'Play sound when a call started (requires Nextcloud Talk)') }}
		</NcCheckboxRadioSwitch>
	</NcSettingsSection>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { generateOcsUrl } from '@nextcloud/router'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'

const EmailFrequency = {
	EMAIL_SEND_OFF: 0,
	EMAIL_SEND_HOURLY: 1,
	EMAIL_SEND_3HOURLY: 2,
	EMAIL_SEND_DAILY: 3,
	EMAIL_SEND_WEEKLY: 4,
}

export default {
	name: 'AdminSettings',
	components: {
		NcCheckboxRadioSwitch,
		NcSettingsSection,
	},

	data() {
		return {
			batchtime_options: [
				{ text: t('notifications', 'Never'), value: EmailFrequency.EMAIL_SEND_OFF },
				{ text: t('notifications', '1 hour'), value: EmailFrequency.EMAIL_SEND_HOURLY },
				{ text: t('notifications', '3 hours'), value: EmailFrequency.EMAIL_SEND_3HOURLY },
				{ text: t('notifications', '1 day'), value: EmailFrequency.EMAIL_SEND_DAILY },
				{ text: t('notifications', '1 week'), value: EmailFrequency.EMAIL_SEND_WEEKLY },
			],

			config: loadState('notifications', 'config'),
		}
	},

	methods: {
		t,

		async updateSettings() {
			try {
				const form = new FormData()
				form.append('batchSetting', this.config.setting_batchtime)
				form.append('soundNotification', this.config.sound_notification ? 'yes' : 'no')
				form.append('soundTalk', this.config.sound_talk ? 'yes' : 'no')
				await axios.post(generateOcsUrl('apps/notifications/api/v2/settings/admin'), form)
				showSuccess(t('notifications', 'Your settings have been updated.'))
			} catch (error) {
				showError(t('notifications', 'An error occurred while updating your settings.'))
				console.error(error)
			}
		},
	},
}

</script>
