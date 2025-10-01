<!--
  - SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcSettingsSection :name="t('notifications', 'Notifications')">
		<div class="notification-frequency__warning">
			<strong v-if="!config.is_email_set">{{ t('notifications', 'You need to set up your email address before you can receive notification emails.') }}</strong>
		</div>
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

		<template v-if="config.sound_talk">
			<NcCheckboxRadioSwitch
				v-model="storage.secondary_speaker"
				class="additional-margin-top"
				:disabled="isSafari"
				@update:model-value="updateLocalSettings">
				{{ t('notifications', 'Also repeat sound on a secondary speaker') }}
			</NcCheckboxRadioSwitch>
			<div v-if="isSafari" class="notification-frequency__warning">
				<strong>{{ t('notifications', 'Selection of the speaker device is currently not supported by Safari') }}</strong>
			</div>
			<NcSelect
				v-if="!isSafari && storage.secondary_speaker"
				v-model="storage.secondary_speaker_device"
				input-id="device-selector-audio-output"
				:options="devices"
				label="label"
				:aria-label-combobox="t('notifications', 'Select a device')"
				:clearable="false"
				:placeholder="t('notifications', 'Select a device')"
				@open="initializeDevices"
				@update:model-value="updateLocalSettings" />
		</template>
	</NcSettingsSection>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { generateOcsUrl } from '@nextcloud/router'
import { UAParser } from 'ua-parser-js'
import { computed, reactive, ref } from 'vue'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import BrowserStorage from '../services/BrowserStorage.js'

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
const EMPTY_DEVICE_OPTION = { id: null, label: t('notifications', 'None') }
const parser = new UAParser()
const browser = parser.getBrowser()
const isSafari = browser.name === 'Safari' || browser.name === 'Mobile Safari'

export default {
	name: 'UserSettings',
	components: {
		NcCheckboxRadioSwitch,
		NcSelect,
		NcSettingsSection,
	},

	setup() {
		const config = reactive(loadState('notifications', 'config'))
		const storage = reactive({
			secondary_speaker: BrowserStorage.getItem('secondary_speaker') === 'true',
			secondary_speaker_device: JSON.parse(BrowserStorage.getItem('secondary_speaker_device')) ?? EMPTY_DEVICE_OPTION,
		})
		const devices = ref([])

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

			isSafari,
			config,
			currentBatchTime,
			devices,
			storage,
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
				await axios.post(generateOcsUrl('apps/notifications/api/v2/settings'), form)
				showSuccess(t('notifications', 'Your settings have been updated.'))
			} catch (error) {
				showError(t('notifications', 'An error occurred while updating your settings.'))
				console.error(error)
			}
		},

		updateLocalSettings() {
			try {
				BrowserStorage.setItem('secondary_speaker', this.storage.secondary_speaker)
				if (this.storage.secondary_speaker && this.storage.secondary_speaker_device.id) {
					BrowserStorage.setItem('secondary_speaker_device', JSON.stringify(this.storage.secondary_speaker_device))
				} else {
					BrowserStorage.removeItem('secondary_speaker_device')
				}
				showSuccess(t('notifications', 'Your settings have been updated.'))
			} catch (error) {
				showError(t('notifications', 'An error occurred while updating your settings.'))
				console.error(error)
			}
		},

		async initializeDevices() {
			const isAudioSupported = !isSafari && navigator?.mediaDevices?.getUserMedia && navigator?.mediaDevices?.enumerateDevices
			if (!isAudioSupported || this.devices.length > 0) {
				return
			}

			let stream = null
			try {
				// Request permissions to get audio devices
				stream = await navigator.mediaDevices.getUserMedia({ audio: true })
				// Enumerate devices and populate NcSelect options
				this.devices = (await navigator.mediaDevices.enumerateDevices() ?? [])
					.filter((device) => device.kind === 'audiooutput')
					.map((device) => ({
						id: device.deviceId,
						label: device.label ? device.label : device.fallbackLabel,
					}))
					.concat([EMPTY_DEVICE_OPTION])
			} catch (error) {
				showError(t('notifications', 'An error occurred while updating your settings.'))
				console.error('Error while requesting or initializing audio devices: ', error)
			} finally {
				if (stream) {
					stream.getTracks().forEach((track) => track.stop())
				}
			}
		},
	},
}

</script>

<style lang="scss" scoped>
.additional-margin-top {
	margin-top: 12px;
}

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
