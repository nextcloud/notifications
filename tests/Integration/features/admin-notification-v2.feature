# SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: admin-notification
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Create notification
    Given user "admin" sends admin notification to "test1" with
      | shortMessage | without long message |
    Then user "test1" has 1 notifications on v2
    And last notification on v2 matches
      | app | admin_notifications |
      | subject | without long message |
      | link | |
      | message | |
      | object_type | admin_notifications |

  Scenario: Create different notification
    Given user "admin" sends admin notification to "test1" with
      | shortMessage | with long message |
      | longMessage | this is long message |
    Then user "test1" has 1 notifications on v2
    And last notification on v2 matches
      | app | admin_notifications |
      | subject | with long message |
      | link | |
      | message | this is long message |
      | object_type | admin_notifications |
