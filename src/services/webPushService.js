/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import axios from '@nextcloud/axios'
import { generateOcsUrl, generateUrl } from '@nextcloud/router'

/**
 * Load service worker
 */
function loadServiceWorker() {
	let scope = getRootUrl()
	// If the instance is not in a subfolder an empty string will be returned.
	// The service worker registration will use the current path if it receives an empty string,
	// which will result in a service worker registration for every single path the user visits.
	if (scope === '') {
		scope = '/'
	}
	return navigator.serviceWorker.register(
		generateUrl('/apps/notifications/service-worker.js', {}, { noRewrite: true }),
		{ scope: scope },
	).then((registration) => {
		console.info('ServiceWorker registered')
		return registration
	})
}
/**
 * Set Push notification Listener
 *
 * @param {ServiceWorkerRegistration} registration current SW registration
 * @param {function(boolean)} onActivated called when we send activation token
 * @param {function()} onPush called when we receive a push notification
 */
function listenForPush(registration, onActivated, onPush) {
	navigator.serviceWorker.addEventListener('message', (event) => {
		console.debug('Received from serviceWorker: ', JSON.stringify(event.data))
		if (event.data.type === 'push') {
			const activationToken = event.data.content.activationToken
			if (activationToken) {
				const form = new FormData()
				form.append('activationToken', activationToken)
				axios.post(generateOcsUrl('apps/notifications/api/v2/webpush/activate'), form)
					.then((response) => {
						onActivated(response.status === 200 || response.status === 202)
					})
			} else {
				onPush()
			}
		} else if (event.data.type === 'pushEndpoint') {
			registerPush(registration)
				.catch((er) => console.error(er))
		}
	})
}

/**
 *
 * @param {ServiceWorkerRegistration} registration current SW registration
 */
function registerPush(registration) {
	return axios.get(generateOcsUrl('apps/notifications/api/v2/webpush/vapid'))
		.then((response) => response.data.ocs.data.vapid)
		.then((vapid) => {
			console.log('Server vapid key=' + vapid)
			const options = {
				applicationServerKey: vapid,
				userVisibleOnly: false,
			}
			return registration.pushManager.getSubscription().then((sub) => {
				if (sub !== null && b64UrlEncode(sub.options.applicationServerKey) !== vapid) {
					console.log('VAPID key changed, unsubscribing first')
					return sub.unsubscribe().then(() => {
						console.log('Unsubscribed')
						return registration.pushManager.subscribe(options)
							.catch((er) => {
								if (er.name === 'NotAllowedError') {
									// if push subscription should set `userVisibleOnly` options (for Chrome)
									console.log('Browser probably require `userVisibleOnly=true`')
									return registration.pushManager.subscribe({ ...options, userVisibleOnly: true })
								} else {
									throw er
								}
							})
					})
				} else {
					return registration.pushManager.subscribe(options)
						.catch((er) => {
							if (er.name === 'NotAllowedError') {
								// if push subscription should set `userVisibleOnly` options (for Chrome)
								console.log('Browser probably require `userVisibleOnly=true`')
								return registration.pushManager.subscribe({ ...options, userVisibleOnly: true })
							} else {
								throw er
							}
						})
				}
			})
		}).then((sub) => {
			console.log(sub)
			const form = new FormData()
			form.append('endpoint', sub.endpoint)
			form.append('uaPublicKey', b64UrlEncode(sub.getKey('p256dh')))
			form.append('auth', b64UrlEncode(sub.getKey('auth')))
			form.append('appTypes', 'all')
			return axios.post(generateOcsUrl('apps/notifications/api/v2/webpush'), form)
		})
}

/**
 * @param {function(reload)} onActivated arg=true if the push notifications has been subscribed (statusCode == 200)
 * @param {function()} onPush run everytime we receive a push notification and we need to sync with the server
 */
function setWebPush(onActivated, onPush) {
	if ('serviceWorker' in navigator) {
		loadServiceWorker()
			.then((registration) => {
				listenForPush(registration, onActivated, onPush)
				return registerPush(registration)
			})
			.catch((er) => {
				console.error(er)
				onActivated(false)
			})
	} else {
		onActivated(false)
	}
}

/**
 *
 * @param {Array} inArr input to encode
 */
function b64UrlEncode(inArr) {
	return new Uint8Array(inArr)
		.toBase64({ alphabet: 'base64url' })
		.replaceAll('=', '')
}

export {
	b64UrlEncode,
	listenForPush,
	loadServiceWorker,
	registerPush,
	setWebPush,
}
