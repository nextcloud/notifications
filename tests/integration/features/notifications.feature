Feature: notifications
  Background:
    Given using api version "1"

  Scenario: Read notifications without Notifiers
    Given user "test1" exists
    Given As an "test1"
    When sending "GET" to "/notifications/v1"
    Then the HTTP status code should be "200"
    # "204 No Content" - Because there is no notifier
    And the OCS status code should be "204"

