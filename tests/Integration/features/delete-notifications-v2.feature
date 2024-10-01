# SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: delete-notifications
  Background:
    Given user "test1" exists
    Given user "123456" exists
    Given as user "test1"

  Scenario: Delete first notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications on v2
    And delete first notification on v2
    And status code is 200
    And user "test1" has 2 notifications on v2 missing the first one

  Scenario: Delete first notification as numeric user
    Given as user "123456"
    Given user "123456" has notifications
    Given user "123456" has notifications
    Given user "123456" has notifications
    Then user "123456" has 3 notifications on v2
    And delete first notification on v2
    And status code is 200
    And user "123456" has 2 notifications on v2 missing the first one

  Scenario: Delete same notification twice
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When user "test1" has 3 notifications on v2
    And delete first notification on v2
    And status code is 200
    And delete same notification on v2
    And status code is 200
    And user "test1" has 2 notifications on v2 missing the first one

  Scenario: Delete faulty notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When user "test1" has 3 notifications on v2
    And delete faulty notification on v2
    And status code is 404
    And user "test1" has 3 notifications on v2

  Scenario: Delete last notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications on v2
    And delete last notification on v2
    And status code is 200
    And user "test1" has 2 notifications on v2 missing the last one

  Scenario: Delete all notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications on v2
    And delete all notifications on v2
    And status code is 200
    And user "test1" has 0 notifications on v2

  Scenario: Delete all notifications as numeric user
    Given as user "123456"
    Given user "123456" has notifications
    Given user "123456" has notifications
    Given user "123456" has notifications
    Then user "123456" has 3 notifications on v2
    And delete all notifications on v2
    And status code is 200
    And user "123456" has 0 notifications on v2
