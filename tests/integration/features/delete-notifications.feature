Feature: delete-notifications
  Background:
    Given user "test1" exists
    Given As user "test1"

  Scenario: Delete first notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications
    And delete first notification
    And user "test1" has 2 notifications missing the first one

  Scenario: Delete last notification
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Then user "test1" has 3 notifications
    And delete last notification
    And user "test1" has 2 notifications missing the last one
