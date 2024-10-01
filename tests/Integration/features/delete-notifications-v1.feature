# SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
# SPDX-FileCopyrightText: 2015-2016 ownCloud, Inc.
# SPDX-License-Identifier: CC0-1.0
Feature: delete-notifications
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Delete first notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications on v1
    And delete first notification on v1
    And status code is 200
    And user "test1" has 2 notifications on v1 missing the first one

  Scenario: Delete same notification twice
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When user "test1" has 3 notifications on v1
    And delete first notification on v1
    And status code is 200
    And delete same notification on v1
    And status code is 200
    And user "test1" has 2 notifications on v1 missing the first one

  Scenario: Delete faulty notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When user "test1" has 3 notifications on v1
    And delete faulty notification on v1
    And status code is 404
    And user "test1" has 3 notifications on v1

  Scenario: Delete last notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications on v1
    And delete last notification on v1
    And status code is 200
    And user "test1" has 2 notifications on v1 missing the last one

  Scenario: Delete all notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications on v1
    And delete all notifications on v1
    And status code is 200
    And user "test1" has 0 notifications on v1
