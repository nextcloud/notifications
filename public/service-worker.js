'use strict'

/**
 * @param {string} title shown in UI notification
 */
function showBgNotification(title) {
	self.registration.showNotification(title)
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
				} else if (content.subject) {
					console.debug('No valid client to send notif - showing bg notif')
					showBgNotification(content.subject)
				} else {
					console.warn('No valid client to send notif')
				}
			})
			.catch((err) => {
				console.error("Couldn't send message: ", err)
			}))
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
