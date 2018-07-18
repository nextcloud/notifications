# Notifications

This app provides a backend and frontend for the notification API available in [Nextcloud](https://github.com/nextcloud/server/).
The API is used by other apps to notify users in the web UI and sync clients about various things. Some examples are:

* 📬 [Federated file sharing](https://github.com/nextcloud/server/tree/master/apps/federatedfilesharing): You received a new remote share
* 📑 [Comments](https://github.com/nextcloud/server/tree/master/apps/federatedfilesharing): Another user mentioned you in a comment on a file
* 🚢 [Update notification](https://github.com/nextcloud/server/tree/master/apps/updatenotification): Available update for an app or nextcloud itself
* 📣 [Announcement center](https://github.com/nextcloud/announcementcenter): An announcement was posted by an admin


## Screenshot

![Screenshot of the notification icon and dropdown](https://raw.githubusercontent.com/nextcloud/notifications/master/docs/screenshot.png)


## Notification sounds
The sound files are licensed CC0 and were created by [feandesign](https://soundcloud.com/feandesign/sets/librem-5-sounds) for the Librem5 sound contest.


## Developers

### Build the notifications app

To set up this app for development, you need to run `make dev-setup` from within the app´s root folder. If anytime later you need to rebuild the javascript files the quicker `make build-js` is enough.

### Creating notifications for your app

For information how to make your app interact with the notifications app, see
[Sending and processing/"mark as read" notifications as a Nextcloud App](https://github.com/nextcloud/notifications/blob/master/docs/notification-workflow.md)
in the wiki.

If you want to present notifications as a client, see [Reading and deleting notifications as an Nextcloud Client](https://github.com/nextcloud/notifications/blob/master/docs/ocs-endpoint-v1.md).
