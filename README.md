# Notifications

An app that notifies the user about important events of other apps.

## QA metrics on master branch:

[![Build Status](https://travis-ci.org/nextcloud/notifications.svg?branch=master)](https://travis-ci.org/nextcloud/notifications)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nextcloud/notifications/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nextcloud/notifications/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nextcloud/notifications/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nextcloud/notifications/?branch=master)

## Screenshots

### No notifications (Sample)

**Note:**
The app hides itself, when there is no app registered, that creates
notifications. In this case the bell and the dropdown are not accessible.

![Build Status](img/sample-empty.png)

### New notifications (Sample)

![Build Status](img/sample-new.png)

## Notification workflow

For information how to make your app interact with the notifications app, see
[Sending and processing/"mark as read" notifications as an Nextcloud App](https://github.com/nextcloud/notifications/blob/master/docs/notification-workflow.md)
in the wiki.

If you want to present notifications as a client, see [Reading and deleting notifications as an Nextcloud Client](https://github.com/nextcloud/notifications/blob/master/docs/ocs-endpoint-v1.md).
