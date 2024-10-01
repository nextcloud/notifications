# SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: Push registration
  Background:
    Given user "test1" exists
    Given as user "test1"

  Scenario: Invalid push token hash
    Given user "test1" registers for push notifications with
    | pushTokenHash | 12345 |
    | devicePublicKey | INVALID_KEY |
    | proxyServer | nextcloud |
    Then error "INVALID_PUSHTOKEN_HASH" is expected with status code 400

  Scenario: Invalid device key
    Given user "test1" registers for push notifications with
    | pushTokenHash | 12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678 |
    | devicePublicKey | INVALID_KEY |
    | proxyServer | nextcloud |
    Then error "INVALID_DEVICE_KEY" is expected with status code 400

  Scenario: Invalid proxy server
    Given user "test1" registers for push notifications with
    | pushTokenHash | 12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678 |
    | devicePublicKey | VALID_KEY |
    | proxyServer | nextcloud |
    Then error "INVALID_PROXY_SERVER" is expected with status code 400

  Scenario: Invalid session token: not using an app password
    Given user "test1" registers for push notifications with
    | pushTokenHash | 12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678 |
    | devicePublicKey | VALID_KEY |
    | proxyServer | https://push-notifications.nextcloud.com/ |
    Then error "INVALID_SESSION_TOKEN" is expected with status code 400

  Scenario: Successful registration
    Given user "test1" creates an app password
    Given user "test1" registers for push notifications with
    | pushTokenHash | 12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678 |
    | devicePublicKey | VALID_KEY |
    | proxyServer | https://push-notifications.nextcloud.com/ |
    Then status code is 201
    And can validate the response and skip verifying signature

  Scenario: Unregistering from push notifications without app password
    Given user "test1" forgets the app password
    Given user "test1" unregisters from push notifications
    Then error "INVALID_SESSION_TOKEN" is expected with status code 400

  Scenario: Unregistering from push notifications successfully
    Given user "test1" creates an app password
    Given user "test1" registers for push notifications with
      | pushTokenHash | 12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678 |
      | devicePublicKey | VALID_KEY |
      | proxyServer | https://push-notifications.nextcloud.com/ |
    Then status code is 201
    And can validate the response and skip verifying signature
    Given user "test1" unregisters from push notifications
    Then status code is 202
    Given user "test1" unregisters from push notifications
    Then status code is 200

  Scenario: Unregistering from push notifications without registering
    Given user "test1" creates an app password
    Given user "test1" unregisters from push notifications
    Then status code is 200
