Feature: notifications
  Background:
    Given using api version "2"

  Scenario: Read notifications without Notifiers
    Given user "test1" exists
    Given As an "test1"
    Given list of notifiers is not empty
    When sending "GET" to "/apps/notifications/api/v1/notifications"
    # "200 OK" - Because there is no notifier
    Then the HTTP status code should be "200"

  Scenario: Read notifications with Notifiers
    Given user "test1" exists
    Given As an "test1"
    Given list of notifiers is empty
    When sending "GET" to "/apps/notifications/api/v1/notifications"
    # "204 No Content" - Because there is no notifier
    Then the HTTP status code should be "204"

