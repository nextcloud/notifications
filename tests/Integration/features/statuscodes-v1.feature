# SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
# SPDX-FileCopyrightText: 2015-2016 ownCloud, Inc.
# SPDX-License-Identifier: CC0-1.0
Feature: statuscodes
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Status code when reading notifications with notifiers and without notifications
    When getting notifications on v1
    Then status code is 200
    And list of notifications has 0 entries

  Scenario: Status code when reading notifications with notifiers and notification
    Given user "test1" has notifications
    When getting notifications on v1
    Then status code is 200
    And list of notifications has 1 entries

  Scenario: Status code when reading notifications with notifiers and notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When getting notifications on v1
    Then status code is 200
    And list of notifications has 3 entries

  Scenario: Status code when reading notifications with different etag
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When getting notifications on v1
    Then status code is 200
    And list of notifications has 3 entries
    When getting notifications on v1 with different etag
    Then status code is 200
    And list of notifications has 3 entries

  Scenario: Status code when reading notifications with matching etag
    Given user "test1" has notifications
    Given user "test1" has notifications
    Given user "test1" has notifications
    When getting notifications on v1
    Then status code is 200
    And list of notifications has 3 entries
    When getting notifications on v1 with matching etag
    # Then status code is 304 - Disabled because it's not listed in the API specs
    Then status code is 200
    # And response body is empty - Disabled because it's not listed in the API specs
    And list of notifications has 3 entries
