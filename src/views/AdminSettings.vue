<!--
  - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcSettingsSection
		:name="t('notifications', 'Notifications defaults')"
		:description="t('notifications', 'Configure the default notification settings')">
		<div class="notification-frequency__wrapper">
			<label for="notification_reminder_batchtime" class="notification-frequency__label">
				{{ t('notifications', 'Send email reminders about unhandled notifications after:') }}
			</label>
			<NcSelect
				id="notification_reminder_batchtime"
				v-model="currentBatchTime"
				class="notification-frequency__select"
				:clearable="false"
				label-outside
				:options="BATCHTIME_OPTIONS"
				@update:model-value="updateSettings" />
		</div>

		<NcCheckboxRadioSwitch
			v-model="config.sound_notification"
			@update:model-value="updateSettings">
			{{ t('notifications', 'Play sound when a new notification arrives') }}
		</NcCheckboxRadioSwitch>
		<NcCheckboxRadioSwitch
			v-model="config.sound_talk"
			@update:model-value="updateSettings">
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
import { computed, reactive } from 'vue'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'

const EmailFrequency = {
	EMAIL_SEND_OFF: 0,
	EMAIL_SEND_HOURLY: 1,
	EMAIL_SEND_3HOURLY: 2,
	EMAIL_SEND_DAILY: 3,
	EMAIL_SEND_WEEKLY: 4,
}

const BATCHTIME_OPTIONS = [
	{ label: t('notifications', 'Never'), value: EmailFrequency.EMAIL_SEND_OFF },
	{ label: t('notifications', '1 hour'), value: EmailFrequency.EMAIL_SEND_HOURLY },
	{ label: t('notifications', '3 hours'), value: EmailFrequency.EMAIL_SEND_3HOURLY },
	{ label: t('notifications', '1 day'), value: EmailFrequency.EMAIL_SEND_DAILY },
	{ label: t('notifications', '1 week'), value: EmailFrequency.EMAIL_SEND_WEEKLY },
]

export default {
	name: 'AdminSettings',
	components: {
		NcSelect,
		NcCheckboxRadioSwitch,
		NcSettingsSection,
	},

	setup() {
		const config = reactive(loadState('notifications', 'config', {}))

		const currentBatchTime = computed({
			get() {
				return BATCHTIME_OPTIONS.find(({ value }) => value === config.setting_batchtime)
			},
			set({ value }) {
				config.setting_batchtime = value
			},
		})

		return {
			BATCHTIME_OPTIONS,
			config,
			currentBatchTime,
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

<style lang="scss" scoped>
.notification-frequency__wrapper {
	display: flex;
	flex-direction: column;
	gap: var(--default-grid-baseline);

	.notification-frequency__select {
		margin-inline-start: calc(2 * var(--default-grid-baseline));
		width: fit-content;
	}
}
</style>
