# SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: notifications-content
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Create notification
    Given user "test1" receives notification with
      | app | notificationsintegrationtesting |
      | timestamp | 144958517 |
      | subject | Integration testing |
      | link | https://example.tld/blog/about-notifications/ |
      | message | About Activities and Notifications in ownCloud |
      | object_type | blog |
      | object_id | 9483 |
    Then user "test1" has 1 notifications on v2
    And last notification on v2 matches
      | app | notificationsintegrationtesting |
      | datetime | 1974-08-05T18:15:17+00:00 |
      | subject | Integration testing |
      | link | https://example.tld/blog/about-notifications/ |
      | message | About Activities and Notifications in ownCloud |
      | object_type | blog |
      | object_id | 9483 |

  Scenario: Create different notification
    Given user "test1" receives notification with
      | app | notificationsintegrationtesting |
      | timestamp | 144958515 |
      | subject | Testing integration |
      | link | https://github.com/nextcloud/notifications/blob/master/docs/ocs-endpoint-v1.md |
      | message | Reading and deleting notifications as a Client |
      | object_type | repo |
      | object_id | notifications |
    Then user "test1" has 1 notifications on v2
    And last notification on v2 matches
      | app | notificationsintegrationtesting |
      | datetime | 1974-08-05T18:15:15+00:00 |
      | subject | Testing integration |
      | link | https://github.com/nextcloud/notifications/blob/master/docs/ocs-endpoint-v1.md |
      | message | Reading and deleting notifications as a Client |
      | object_type | repo |
      | object_id | notifications |
