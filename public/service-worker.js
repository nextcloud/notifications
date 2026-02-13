'use strict'

/**
 * @param {string} content received by Nextcloud, splitted to be shown in UI notification
 */
function showBgNotification(content) {
	let [title, ...msg] = content.split('\n')
	const options = {
		body: msg.join('\n'),
	}
	self.registration.showNotification(title, options)
}

/**
 * For Chrom* and Apple (who followed Chrome) users:
 * We need to show a silent notification that we remove to avoid being unregistered,
 * because they require userVisibleOnly=true registrations, and so forbid silent notifs.
 */
function silentNotificationHack() {
	const tag = (Math.random() + 1).toString(36).substring(2)
	const options = {
		silent: true,
		tag: tag,
	}
	self.registration.showNotification("Got a background event", options).then(() =>{
		const options = {
			tag: tag
		}
		return self.registration.getNotifications(options)
	}).then((notification) => {
		if (notification[0]) {
			notification[0].close()
		}
	});
}

self.addEventListener('push', function(event) {
	console.info('Received push message')

	if (event.data) {
		const content = event.data.json()
		console.log('Got ', content)
		// Send the event to the last focused page only,
		// show a notification with the subject if we don't have any
		// active tab
		event.waitUntil(self.clients.matchAll()
			.then((clientList) => {
				const client = clientList[0]
				if (client !== undefined) {
					console.debug('Sending to client ', client)
					client.postMessage({ type: 'push', content })
					// Here, the user has an active tab, we don't need to show a notification from the sw
				} else if (content.subject) {
					console.debug('No valid client to send notif - showing bg notif')
					showBgNotification(content.subject)
				} else {
					console.warn('No valid client to send notif')
					silentNotificationHack()
				}
			})
			.catch((err) => {
				console.error("Couldn't send message: ", err)
				silentNotificationHack()
			}))
	} else {
		silentNotificationHack()
	}
})

self.addEventListener('pushsubscriptionchange', function(event) {
	console.log('Push Subscription Change', event)
	event.waitUntil(self.clients.matchAll()
		.then((clientList) => {
			const client = clientList[0]
			if (client !== undefined) {
				console.debug('Sending to client ', client)
				client.postMessage({ type: 'pushEndpoint' })
			} else {
				console.warn('No valid client to send notif')
			}
		})
		.catch((err) => {
			console.error("Couldn't send message: ", err)
		}))
})

self.addEventListener('registration', function() {
	console.log('Registered')
})

self.addEventListener('install', function(event) {
	console.log('Installed')
	// Replace currenctly active serviceWorkers with this one
	event.waitUntil(self.skipWaiting())
})

self.addEventListener('activate', function(event) {
	console.log('Activated')
	// Ensure we control the clients
	event.waitUntil(self.clients.claim())
})
