# SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: cli-notification
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Create notification
    Given invoking occ with "table"
     |                    | notification:generate                                              |
     |                    | test1                                                              |
     |                    | {packages} require to be updated                                   |
     | --short-parameters | {"packages":{"type":"highlight","id":"count","name":"1 packages"}} |
     | --long-message     | Packages to update: {list}                                         |
     | --long-parameters  | {"list":{"type":"highlight","id":"list","name":"package1"}}        |
     | --object-type      | update                                                             |
     | --object-id        | apt                                                                |
     | --output-id-only   |                                                                    |
    And the command was successful
    Then user "test1" has 1 notifications on v2
    And last notification on v2 matches
      | app | admin_notifications |
      | subject | 1 packages require to be updated |
      | message | Packages to update: package1 |
      | object_type | update |
      | object_id | apt |
    Given invoking occ with "table"
     |                    | notification:generate                                              |
     |                    | test1                                                              |
     |                    | {packages} require to be updated                                   |
     | --short-parameters | {"packages":{"type":"highlight","id":"count","name":"2 packages"}} |
     | --long-message     | Packages to update: {list}                                         |
     | --long-parameters  | {"list":{"type":"highlight","id":"list","name":"package1, package2"}} |
     | --object-type      | update                                                             |
     | --object-id        | apt                                                                |
     | --output-id-only   |                                                                    |
    And the command was successful
    Then user "test1" has 1 notifications on v2
    And last notification on v2 matches
      | app | admin_notifications |
      | subject | 2 packages require to be updated |
      | message | Packages to update: package1, package2 |
      | object_type | update |
      | object_id | apt |
    Given invoking occ with "notification:delete test1 {LAST_COMMAND_OUTPUT}"
    And the command was successful
    Then user "test1" has 0 notifications on v2
