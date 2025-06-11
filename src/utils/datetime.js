/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { getCanonicalLocale } from '@nextcloud/l10n'

const locale = getCanonicalLocale()

const absoluteTimeFormat = new Intl.DateTimeFormat(locale, {
	year: 'numeric',
	month: 'long',
	day: 'numeric',
	hour: 'numeric',
	minute: 'numeric',
})
// FIXME: Intl.RelativeTimeFormat should use getLanguage(), not getCanonicalLocale()
const relativeTimeFormat = new Intl.RelativeTimeFormat(locale, {
	numeric: 'auto',
})

/**
 * Format given timestamp in human-readable format (01 January 2025 12:00)
 * If the unit is not provided, the largest unit is used for rounded duration in milliseconds.
 *
 * @param {number} timestamp - Timestamp in ms
 */
export function formatDateTime(timestamp) {
	return absoluteTimeFormat.format(new Date(timestamp))
}

/**
 * Format relative time duration in human-readable format from now
 *
 * @param {number} timestamp - Timestamp in ms
 */
export function formatRelativeTimeFromNow(timestamp) {
	return formatRelativeTime(timestamp - Date.now())
}

/**
 * Format relative time duration in human-readable format
 *
 * @param {number} ms - Duration in milliseconds
 */
function formatRelativeTime(ms) {
	const { value, unit } = convertMsToLargestTimeUnit(ms)

	return relativeTimeFormat.format(value, unit)
}

/**
 * Convert milliseconds to the largest unit rounded from 0.75 point.
 *
 * @example 123 -> { value: 0, unit: 'second' }
 * @example 1000 -> { value: 1, unit: 'second' }
 * @example 25 * 60 * 60 * 1000 -> { value: 25, unit: 'minute' }
 * @example 35 * 60 * 60 * 1000 -> { value: 35, unit: 'minute' }
 * @example 45 * 60 * 60 * 1000 -> { value: 1, unit: 'hour' }
 * @example 3600000 -> { value: 1, unit: 'hour' }
 * @example 86400000 -> { value: 1, unit: 'day' }
 * @param {number} ms - Duration in milliseconds
 */
function convertMsToLargestTimeUnit(ms) {
	const units = {
		year: 0,
		month: 0,
		day: 0,
		hour: 0,
		minute: 0,
		second: 0,
	}

	units.second = ms / 1000
	units.minute = units.second / 60
	units.hour = units.minute / 60
	units.day = units.hour / 24
	units.month = units.day / 30
	units.year = units.day / 365

	const round = (value) => Math.abs(value % 1) < 0.75 ? Math.trunc(value) : Math.round(value)

	// Loop from the largest unit to the smallest
	for (const key in units) {
		const unit = key
		// Round the value so 59 min 59 sec 999 ms is 1 hour and not 59 minutes
		const rounded = round(units[unit])
		// Return the first non-zero unit
		if (rounded !== 0) {
			return {
				value: rounded,
				unit,
			}
		}
	}

	// now
	return {
		value: 0,
		unit: 'second',
	}
}
