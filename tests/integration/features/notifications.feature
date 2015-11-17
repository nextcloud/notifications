Feature: notifications
  Background:
    Given using api version "1"

  Scenario: Creating a new notification
    Given user "user0" exists
    When sending "POST" to "/apps/notifications/v1/notifications" with
      | subject | test |
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"

