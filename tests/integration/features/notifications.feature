Feature: notifications
  Background:
    Given using api version "1"

  Scenario: Create test user
    Given As an "admin"
    And user "test1" does not exist
    When sending "POST" to "/cloud/users" with
      | userid | test1 |
      | password | 123456 |
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "test1" exists

  Scenario: Creating a new notification
    Given As an "test1"
    When sending "GET" to "/notifications/v1"
    Then the HTTP status code should be "200"
    # 204 No Content - Because there is no notifier
    And the OCS status code should be "204"

