/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import BrowserStorage from './BrowserStorage.js'

/**
 * @param {string|null} tabId unique id for browser tab
 * @param {string} lastETag last ETag
 * @param {boolean} forceRefresh whether to refresh data forcefully
 * @param {boolean} hasNotifyPush whether has notify_push enabled
 */
async function getNotificationsData(tabId, lastETag, forceRefresh, hasNotifyPush) {
	const lastUpdated = parseInt(BrowserStorage.getItem('lastUpdated'), 10)
	const lastTab = BrowserStorage.getItem('tabId')
	const now = Math.floor(Date.now() / 1000)

	if (forceRefresh
		// Allow the same tab to refresh with less than the timeout,
		|| (lastTab === tabId && lastUpdated + 25 < now)
		// Allow the same tab to refresh with notify push,
		|| (lastTab === tabId && hasNotifyPush)
		// and at the same time give it some more time against other tabs.
		|| lastUpdated + 35 < now) {
		BrowserStorage.setItem('tabId', tabId)
		BrowserStorage.setItem('lastUpdated', now.toString())
		// console.debug('Refetching data in ' + tabId + ' (prev: ' + lastTab + ' age: ' + (now - lastUpdated) + ')')
		await refreshData(lastETag)
	// } else {
	// console.debug('Reusing data in ' + tabId + ' (prev: ' + lastTab + ' age: ' + (now - lastUpdated) + ')')
	}

	return {
		status: parseInt(BrowserStorage.getItem('status'), 10),
		headers: JSON.parse(BrowserStorage.getItem('headers') || '[]'),
		data: JSON.parse(BrowserStorage.getItem('data') || '[]'),
		tabId: BrowserStorage.getItem('tabId'),
		lastUpdated: parseInt(BrowserStorage.getItem('lastUpdated'), 10),
	}
}

/**
 * @param {string|null} tabId unique id for browser tab
 */
async function setCurrentTabAsActive(tabId) {
	const lastTab = BrowserStorage.getItem('tabId')
	if (lastTab !== tabId) {
		// Refresh the data when changing the tab
		await getNotificationsData(tabId, '', true, false)
		// Enforce this tab one the raise-condition if there was one
		BrowserStorage.setItem('tabId', tabId)
	}
}

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
async function refreshData(lastETag) {
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

		BrowserStorage.setItem('status', '' + response.status)
		if (response.status !== 204) {
			BrowserStorage.setItem('headers', JSON.stringify(response.headers))
			BrowserStorage.setItem('data', JSON.stringify(response.data.ocs.data.map(remapAttributes)))
		}
	} catch (error) {
		if (error?.response?.status) {
			BrowserStorage.setItem('status', '' + error.response.status)
		} else {
			// Setting to 500 in case no request was made so it's retried on the next attempt
			BrowserStorage.setItem('status', '500')
		}
	}
}

export {
	getNotificationsData,
	setCurrentTabAsActive,
}
