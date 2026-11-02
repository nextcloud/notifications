# SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: admin-notification-v3
  Background:
    Given user "test1" exists
    Given user "test2" exists
    Given as user "test1"

  Scenario: Non-admin user creates a notification for themselves
    When sending "POST" to "/apps/notifications/api/v3/admin_notifications/test1?format=json" with
      | subject | self notification |
      | message | this is the message |
    Then status code is 200
    And user "test1" has 1 notifications on v2
    And last notification on v2 matches
      | app | admin_notifications |
      | subject | self notification |
      | message | this is the message |
      | object_type | admin_notifications |

  Scenario: Non-admin user can not create a notification for another user
    When sending "POST" to "/apps/notifications/api/v3/admin_notifications/test2?format=json" with
      | subject | foreign notification |
    Then status code is 400
    Given as user "test2"
    Then user "test2" has 0 notifications on v2

  Scenario: Admin creates a notification for another user
    Given as user "admin"
    When sending "POST" to "/apps/notifications/api/v3/admin_notifications/test1?format=json" with
      | subject | admin notification |
    Then status code is 200
    Given as user "test1"
    Then user "test1" has 1 notifications on v2
    And last notification on v2 matches
      | app | admin_notifications |
      | subject | admin notification |
      | object_type | admin_notifications |
