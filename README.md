<!--
  - SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
  - SPDX-FileCopyrightText: 2015-2016 ownCloud, Inc.
  - SPDX-License-Identifier: AGPL-3.0-only
-->

# Summary

The Nextcloud Notifications app is a critical component of the Nextcloud ecosystem, designed to enhance user engagement and streamline communication across various applications within the platform. The primary objective of this project is to provide a unified notification system that allows different Nextcloud apps to send real-time updates to users, ensuring that they remain informed about important events and actions. 

The app is fully integrated into the Nextcloud platform, ensuring compatibility with various apps and services. Developers can define different types of notifications for their apps, ensuring that users receive relevant and actionable updates. Notifications are delivered instantly, allowing users to take immediate action when needed. The app works seamlessly across the Nextcloud web UI, desktop clients, and mobile applications, ensuring consistent notifications across all devices. 

A clean and intuitive notification panel allows users to view, filter, and manage their notifications efficiently. Security and privacy are prioritized, ensuring that notifications are delivered securely and with respect to user privacy settings and permissions. 

The app plays a vital role in various use cases, such as collaborative workflows, where users get notified when someone shares a file, comments on a document, or mentions them in a discussion; system administration, where admins receive updates about security warnings, app updates, and critical system events; task management, where users stay on top of their schedules with reminders about meetings, deadlines, and assigned tasks; and cloud synchronization, where users are kept informed about file sync progress and potential conflicts. The development team is actively working on future enhancements, including enhanced filtering options to categorize and prioritize notifications based on relevance, push notifications for mobile integration to ensure timely alerts on the go, advanced customization options to give users more control over which notifications are received and how they are displayed, and expanding API capabilities for better integration with third-party applications. By providing a robust and extensible notification system, this app significantly improves user experience, making Nextcloud an even more powerful collaboration platform.

# Notifications

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/notifications)](https://api.reuse.software/info/github.com/nextcloud/notifications)

This app provides a backend and frontend for the notification API available in Nextcloud. The API is used by other apps to notify users in the web UI and sync clients about various things.

With this app, users receive real-time updates on relevant activities, keeping them informed about important system events, file-sharing activities, and collaborative interactions. The notification system ensures that users never miss critical updates, enhancing productivity and communication within the Nextcloud ecosystem.

# Examples of Notifications

* 📬 [Federated file sharing](https://github.com/nextcloud/server/tree/master/apps/federatedfilesharing): Receive notifications when a new remote share is available, ensuring you stay updated on external file collaborations.
  
* 📑 [Comments](https://github.com/nextcloud/server/tree/master/apps/comments): Get alerted when another user mentions you in a comment on a file, making it easier to track discussions and feedback.
  
* 🚢 [Update notification](https://github.com/nextcloud/server/tree/master/apps/updatenotification): Be notified when a new update is available for an app or Nextcloud itself, ensuring your system stays secure and up to date.
  
* 📣 [Announcement center](https://github.com/nextcloud/announcementcenter): AReceive important announcements posted by an admin, keeping users informed of critical updates and changes.

* 🔔 Mentions and Direct Messages: Get notified when someone directly mentions you in a document, chat, or shared task, facilitating seamless communication.

* 📂 File Modifications: Receive instant alerts when a shared file is edited, renamed, or deleted by another user.

* 🔄 Synchronization Alert******s**: Stay updated on the status of file sync operations between devices and the cloud, helping users manage their storage effectively.

* 🔧 System Warnings: Get notified of potential security risks, storage limits, or other administrative messages that require immediate attention.

* ✅ Task and Calendar Reminders: Receive alerts for upcoming deadlines, scheduled meetings, or assigned tasks to stay organized.

By integrating seamlessly with the Nextcloud ecosystem, this notification system ensures that users remain informed and engaged with their collaborative environment, improving workflow efficiency and enhancing the user experience.

## Screenshot

![Screenshot of the notification icon and dropdown](https://raw.githubusercontent.com/nextcloud/notifications/master/docs/screenshot.png)

## Developers

### Install and enable the notifications app

- Clone this app into the "apps" folder of your Nextcloud instance.
```bash
git clone https://github.com/nextcloud/notifications.git
