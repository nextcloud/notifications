# SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: CC0-1.0
Feature: WebPush registration
  Background:
    Given user "test1" exists
    Given as user "test1"
    Given webpush is enabled

  Scenario: Get VAPID public key
    When user "test1" fetches the VAPID public key
    Then status code is 200
    And the VAPID key is not empty

  Scenario: Register with invalid P256dh key
    Given user "test1" creates an app password
    Given user "test1" registers for webpush with
      | endpoint | https://push.example.com/test |
      | uaPublicKey | INVALID_KEY |
      | auth | VALID_AUTH |
      | appTypes | all |
    Then error "INVALID_P256DH" is expected with status code 400

  Scenario: Register with invalid auth
    Given user "test1" creates an app password
    Given user "test1" registers for webpush with
      | endpoint | https://push.example.com/test |
      | uaPublicKey | VALID_KEY |
      | auth | bad |
      | appTypes | all |
    Then error "INVALID_AUTH" is expected with status code 400

  Scenario: Register with invalid endpoint
    Given user "test1" creates an app password
    Given user "test1" registers for webpush with
      | endpoint | http://not-https.example.com/test |
      | uaPublicKey | VALID_KEY |
      | auth | VALID_AUTH |
      | appTypes | all |
    Then error "INVALID_ENDPOINT" is expected with status code 400

  Scenario: Register, activate and remove webpush subscription
    Given user "test1" creates an app password
    Given user "test1" registers for webpush with
      | endpoint | https://push.example.com/test |
      | uaPublicKey | VALID_KEY |
      | auth | VALID_AUTH |
      | appTypes | all |
    Then status code is 201
    Given user "test1" activates webpush with the activation token
    Then status code is 202
    Given user "test1" removes webpush subscription
    Then status code is 202
    Given user "test1" removes webpush subscription
    Then status code is 200

  Scenario: Activate with wrong token
    Given user "test1" creates an app password
    Given user "test1" registers for webpush with
      | endpoint | https://push.example.com/test |
      | uaPublicKey | VALID_KEY |
      | auth | VALID_AUTH |
      | appTypes | all |
    Then status code is 201
    Given user "test1" activates webpush with token "wrong-token"
    Then error "INVALID_ACTIVATION_TOKEN" is expected with status code 400

  Scenario: Activate without subscription
    Given user "test1" creates an app password
    Given user "test1" activates webpush with token "any-token"
    Then error "NO_PUSH_SUBSCRIPTION" is expected with status code 404

  Scenario: Remove without subscription
    Given user "test1" creates an app password
    Given user "test1" removes webpush subscription
    Then status code is 200

  Scenario: Register when webpush is disabled
    Given webpush is disabled
    Given user "test1" creates an app password
    Given user "test1" registers for webpush with
      | endpoint | https://push.example.com/test |
      | uaPublicKey | VALID_KEY |
      | auth | VALID_AUTH |
      | appTypes | all |
    Then error "WEBPUSH_DISABLED" is expected with status code 403

  Scenario: Don't allow registering with local URLs
    Given user "test1" creates an app password
    Given user "test1" registers for webpush with
      | endpoint | http://push.example.com/test |
      | uaPublicKey | VALID_KEY |
      | auth | VALID_AUTH |
      | appTypes | all |
    Then error "INVALID_ENDPOINT" is expected with status code 400
    Given user "test1" registers for webpush with
      | endpoint | http://localhost/test |
      | uaPublicKey | VALID_KEY |
      | auth | VALID_AUTH |
      | appTypes | all |
    Then error "INVALID_ENDPOINT" is expected with status code 400
    Given user "test1" registers for webpush with
      | endpoint | https://localhost/test |
      | uaPublicKey | VALID_KEY |
      | auth | VALID_AUTH |
      | appTypes | all |
    Then error "INVALID_ENDPOINT" is expected with status code 400
