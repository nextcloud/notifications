/**
 * @copyright Copyright (c) 2020 Joas Schilling <coding@schilljs.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

import moment from '@nextcloud/moment'
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import BrowserStorage from './BrowserStorage'

const getNotificationsData = async (tabId, lastETag, forceRefresh) => {
	const lastUpdated = parseInt(BrowserStorage.getItem('lastUpdated'), 10)
	const lastTab = BrowserStorage.getItem('tabId')
	const now = moment().format('X')

	if (forceRefresh
		// Allow the same tab to refresh with less than the timeout,
		|| (lastTab === tabId && lastUpdated + 25 < now)
		// and at the same time give it some more time against other tabs.
		|| lastUpdated + 35 < now) {
		BrowserStorage.setItem('tabId', tabId)
		BrowserStorage.setItem('lastUpdated', now)
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

const refreshData = async (lastETag) => {
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
			BrowserStorage.setItem('data', JSON.stringify(response.data.ocs.data))
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
}
