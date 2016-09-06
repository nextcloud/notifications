Feature: delete-notifications
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Delete first notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications
    And delete first notification
    And status code is 200
    And user "test1" has 2 notifications missing the first one

  Scenario: Delete same notification twice
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When user "test1" has 3 notifications
    And delete first notification
    And status code is 200
    And delete same notification
    And status code is 200
    And user "test1" has 2 notifications missing the first one

  Scenario: Delete faulty notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When user "test1" has 3 notifications
    And delete faulty notification
    And status code is 404
    And user "test1" has 3 notifications

  Scenario: Delete last notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications
    And delete last notification
    And status code is 200
    And user "test1" has 2 notifications missing the last one
