Feature: statuscodes
  Background:
    Given using api version "2"
    Given user "test1" exists
    Given As an "test1"

  Scenario: Status code when reading notifications without notifiers
    Given list of notifiers is empty
    When sending "GET" to "/apps/notifications/api/v1/notifications?format=json"
    # "204 No Content"
    Then the HTTP status code should be "204"
    # Request-Body is empty: And list of notifications has 0 entries

  Scenario: Status code when reading notifications with notifiers and without notifications
    Given list of notifiers is not empty
    When sending "GET" to "/apps/notifications/api/v1/notifications?format=json"
    Then the HTTP status code should be "200"
    And list of notifications has 0 entries

  Scenario: Status code when reading notifications with notifiers and notification
    Given list of notifiers is not empty
    Given user "test1" has notifications
    When sending "GET" to "/apps/notifications/api/v1/notifications?format=json"
    Then the HTTP status code should be "200"
    And list of notifications has 1 entries

  Scenario: Status code when reading notifications with notifiers and notifications
    Given list of notifiers is not empty
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When sending "GET" to "/apps/notifications/api/v1/notifications?format=json"
    Then the HTTP status code should be "200"
    And list of notifications has 3 entries
