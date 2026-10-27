/*!
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

const ALLOWED_PROTOCOLS = ['http:', 'https:']

/**
 * Check that a link is relative or uses the http(s) scheme
 *
 * @param {string} link The link to check
 * @return {boolean}
 */
export function isSafeLink(link) {
	if (!link) {
		return false
	}

	try {
		return ALLOWED_PROTOCOLS.includes(new URL(link, window.location.href).protocol)
	} catch {
		return false
	}
}
