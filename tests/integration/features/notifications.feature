Feature: notifications
  Background:
    Given using api version "2"

  Scenario: Read notifications without Notifiers
    Given user "test1" exists
    Given As an "test1"
    When sending "GET" to "/apps/notifications/api/v1/notifications"
    # "204 No Content" - Because there is no notifier
    Then the HTTP status code should be "204"

