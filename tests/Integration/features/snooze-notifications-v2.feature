# SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: snooze-notifications
  Background:
    Given user "test1" exists
    Given user "test2" exists
    Given as user "test1"

  Scenario: Snoozing a notification hides it from the list and from exists
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications on v2
    And snooze first notification on v2 until 3600
    And status code is 200
    And user "test1" has 2 notifications on v2 missing the first one

  Scenario: Deleting all notifications does not error out with a snoozed notification present
    # A snoozed notification stays hidden from the list either way, so this only
    # checks the request succeeds; that the row itself survives dismiss-all is
    # covered at the database level by HandlerTest::testDeleteAndDeleteByUserCanSpareSnoozed.
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 2 notifications on v2
    And snooze first notification on v2 until 3600
    And status code is 200
    And delete all notifications on v2
    And status code is 200
    And user "test1" has 0 notifications on v2

  Scenario: Snoozing another user's notification is not found
    Given user "test1" has notifications
    Then user "test1" has 1 notifications on v2
    Given as user "test2"
    And snooze other user's notification on v2 until 3600
    And status code is 404

  Scenario: Snoozing a faulty notification is not found
    Given user "test1" has notifications
    Then user "test1" has 1 notifications on v2
    And snooze faulty notification on v2 until 3600
    And status code is 404

  Scenario: A snoozeUntil in the past is rejected
    Given user "test1" has notifications
    Then user "test1" has 1 notifications on v2
    And snooze first notification on v2 until -3600
    And status code is 400
    And user "test1" has 1 notifications on v2
