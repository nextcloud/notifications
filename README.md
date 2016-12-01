# Notifications

This app provides a backend and frontend for the notification API available in [Nextcloud](https://github.com/nextcloud/server/).
The API is used by other apps to notify users in the web UI and sync clients about various things. Some examples are:

* 📬 [Federated file sharing](https://github.com/nextcloud/server/tree/master/apps/federatedfilesharing): You received a new remote shares
* 📑 [Comments](https://github.com/nextcloud/server/tree/master/apps/federatedfilesharing): Another user mentioned you in a comment on a file
* 🚢 [Update notification](https://github.com/nextcloud/server/tree/master/apps/updatenotification): Available update for an app or nextcloud itself
* 📣 [Announcement center](https://github.com/nextcloud/announcementcenter): An announcement was posted by an admin

## QA metrics on master branch:

[![Build Status](https://travis-ci.org/nextcloud/notifications.svg?branch=master)](https://travis-ci.org/nextcloud/notifications)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nextcloud/notifications/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nextcloud/notifications/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nextcloud/notifications/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nextcloud/notifications/?branch=master)

## Screenshot

![Screenshot of the notification icon and dropdown](https://raw.githubusercontent.com/nextcloud/notifications/master/docs/screenshot.png)

**Note:**
The 🔔 icon is hidden, when the user has no notifications.

## Notification workflow

For information how to make your app interact with the notifications app, see
[Sending and processing/"mark as read" notifications as an Nextcloud App](https://github.com/nextcloud/notifications/blob/master/docs/notification-workflow.md)
in the wiki.

If you want to present notifications as a client, see [Reading and deleting notifications as an Nextcloud Client](https://github.com/nextcloud/notifications/blob/master/docs/ocs-endpoint-v1.md).
