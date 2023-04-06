Feature: exists-notifications-v2
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Delete first notification
    Given user "test1" has notifications
    And user "test1" has 1 notifications on v2
    Then confirms previously fetched notification ids exist on v2
