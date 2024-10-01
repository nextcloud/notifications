# SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: exists-notifications-v2
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Check the notification ID is returned on exists-check
    Given user "test1" has notifications
    And user "test1" has 1 notifications on v2
    Then confirms previously fetched notification ids exist on v2
