/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'

/**
 * @param {object} notification notification object
 */
function remapAttributes(notification) {
	notification.notificationId = notification.notification_id
	notification.objectId = notification.object_id
	notification.objectType = notification.object_type

	delete notification.notification_id
	delete notification.object_id
	delete notification.object_type

	return notification
}

/**
 * @param {string} lastETag last ETag
 */
async function getNotificationsData(lastETag) {
	let requestConfig = {}
	if (lastETag) {
		requestConfig = {
			headers: {
				'If-None-Match': lastETag,
			},
		}
	}

	try {
		const response = await axios.get(generateOcsUrl('apps/notifications/api/v2/notifications'), requestConfig)
		if (response.status === 204) {
			return { status: 204 }
		}

		return {
			status: response.status,
			headers: response.headers.toJSON(),
			data: response.data.ocs.data.map(remapAttributes),
		}
	} catch (error) {
		// Status 500 in case no request was made, so it's retried on the next attempt
		return { status: error?.response?.status ?? 500 }
	}
}

export {
	getNotificationsData,
}
