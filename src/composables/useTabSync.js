/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { ref } from 'vue'

/**
 * Elect one leader tab and share data between tabs.
 * Web Locks are only available in a secure context. Without them, every tab is its own leader.
 * A new follower tab gets the last shared data from the leader on start.
 *
 * Messages on the BroadcastChannel:
 * - 'request': sent by a new tab on start, the leader answers with the last shared data
 * - any other value: data shared by a tab with shareData(), or the leader's answer to 'request'
 *
 * shortcut: no cleanup on unmount, the header app is never unmounted; add it when used in a component that unmounts
 *
 * @param {string} name lock and channel name
 */
export function useTabSync(name) {
	const isLeader = ref(false)
	/** @type {BroadcastChannel|null} */
	let channel = null
	let lastData = null

	/**
	 * @param {() => void} onLeader called once, when this tab becomes the leader
	 * @param {(data: object) => void} onData called with the data shared by another tab
	 */
	function startTabSync(onLeader, onData) {
		if (!('locks' in navigator)) {
			isLeader.value = true
			onLeader()
			return
		}

		channel = new BroadcastChannel(name)
		channel.onmessage = (event) => {
			if (event.data === 'request') {
				if (isLeader.value && lastData) {
					channel.postMessage(lastData)
				}
				return
			}
			lastData = event.data
			onData(event.data)
		}
		navigator.locks.request(name, () => {
			isLeader.value = true
			try {
				onLeader()
			} catch (error) {
				console.error(error)
			}
			// Hold the lock until the tab is closed
			return new Promise(() => {})
		})
		channel.postMessage('request')
	}

	/**
	 * @param {object} data data to send to other tabs
	 */
	function shareData(data) {
		lastData = data
		channel?.postMessage(data)
	}

	return {
		isLeader,
		startTabSync,
		shareData,
	}
}
