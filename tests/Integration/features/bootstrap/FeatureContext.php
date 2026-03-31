<?php

/**
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */
require __DIR__ . '/../../vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\AfterScenario;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext {
	/** @var array[] */
	protected $notificationIds;

	/** @var int */
	protected $deletedNotification;

	/** @var string */
	protected $currentUser;

	/** @var ResponseInterface */
	private $response = null;

	/** @var CookieJar */
	private $cookieJar;

	/** @var string */
	protected $baseUrl;

	/** @var string */
	protected $lastEtag;

	/** @var resource */
	protected $deviceKey;

	/** @var string[] */
	protected $appPasswords;

	/** @var string */
	protected $webPushPublicKey;

	/** @var string */
	protected $webPushAuth;

	use CommandLineTrait;

	/**
	 * FeatureContext constructor.
	 */
	public function __construct() {
		$this->cookieJar = new CookieJar();
		$this->baseUrl = getenv('TEST_SERVER_URL');
	}

	#[Given('/^user "([^"]*)" has notifications$/')]
	public function hasNotifications(string $user) {
		$response = $this->setTestingValue('POST', 'apps/notificationsintegrationtesting/notifications?userId=' . $user, null);
		$this->assertStatusCode($response, 200);
	}

	#[Given('/^user "([^"]*)" receives notification with$/')]
	public function receiveNotification(string $user, TableNode $formData) {
		$response = $this->setTestingValue('POST', 'apps/notificationsintegrationtesting/notifications?userId=' . $user, $formData);
		$this->assertStatusCode($response, 200);
	}

	#[Given('/^user "([^"]*)" sends admin notification to "([^"]*)" with$/')]
	public function sendAdminNotification(string $sender, string $recipient, TableNode $formData) {
		$currentUser = $this->currentUser;
		$this->setCurrentUser($sender);
		$this->sendingToWith('POST', '/apps/notifications/api/v2/admin_notifications/' . $recipient . '?format=json', $formData);
		$this->assertStatusCode($this->response, 200);
		$this->setCurrentUser($currentUser);
	}

	#[When('/^getting notifications on (v\d+)(| with different etag| with matching etag)$/')]
	public function gettingNotifications(string $api, string $eTag) {
		$headers = [];
		if ($eTag === ' with different etag') {
			$headers['If-None-Match'] = substr($this->lastEtag ?? '', 0, 16);
		} elseif ($eTag === ' with matching etag') {
			$headers['If-None-Match'] = $this->lastEtag ?? '';
		}

		$this->sendingToWith('GET', '/apps/notifications/api/' . $api . '/notifications?format=json', null, $headers);
		$etagHeaders = $this->response->getHeader('ETag');
		$this->lastEtag = array_pop($etagHeaders);
	}

	#[Then('/^response body is empty$/')]
	public function checkResponseBodyIsEmpty() {
		Assert::assertSame('', $this->response->getBody()->getContents());
	}

	#[Then('/^list of notifications has (\d+) entries$/')]
	public function checkNumNotifications(int $numNotifications) {
		$notifications = $this->getArrayOfNotificationsResponded($this->response);
		Assert::assertCount((int)$numNotifications, $notifications);

		$notificationIds = [];
		foreach ($notifications as $notification) {
			$notificationIds[] = (int)$notification['notification_id'];
		}

		$this->notificationIds[] = $notificationIds;
	}

	#[Then('/^confirms previously fetched notification ids exist on (v\d+)$/')]
	public function checkNotificationsExists(string $api) {
		$notificationIds = end($this->notificationIds);

		$sendingWithGarbage = $notificationIds;
		// An array instead of int
		$sendingWithGarbage[] = $notificationIds;
		// A string instead of int
		$sendingWithGarbage[] = '$notificationIds';
		// A duplicate
		$sendingWithGarbage[] = reset($notificationIds);

		$this->sendingToWith('POST', '/apps/notifications/api/' . $api . '/notifications/exists?format=json', [
			'ids' => $sendingWithGarbage,
		]);

		$this->assertStatusCode($this->response, 200);
		$actualIds = $this->getDataFromOCSResponse($this->response);

		Assert::assertSame($notificationIds, $actualIds);
	}

	protected function getArrayOfNotificationsResponded(ResponseInterface $response): array {
		return $this->getDataFromOCSResponse($response);
	}

	protected function getDataFromOCSResponse(ResponseInterface $response): array {
		$jsonBody = json_decode($response->getBody()->getContents(), true);
		return $jsonBody['ocs']['data'];
	}

	#[Then('/^user "([^"]*)" has (\d+) notifications on (v\d+)(| missing the last one| missing the first one)$/')]
	public function userNumNotifications(string $user, int $numNotifications, string $api, string $missingLast) {
		$this->sendingTo('GET', '/apps/notifications/api/' . $api . '/notifications?format=json');
		$this->assertStatusCode($this->response, 200);

		$previousNotificationIds = [];
		if ($missingLast) {
			Assert::assertNotEmpty($this->notificationIds);
			$previousNotificationIds = end($this->notificationIds);
		}

		$this->checkNumNotifications((int)$numNotifications);

		if ($missingLast) {
			$now = end($this->notificationIds);
			if ($missingLast === ' missing the last one') {
				array_unshift($now, $this->deletedNotification);
			} else {
				$now[] = $this->deletedNotification;
			}

			Assert::assertEquals($previousNotificationIds, $now);
		}
	}

	#[Then('/^(first|last) notification on (v\d+) matches$/')]
	public function matchNotification(string $notification, string $api, ?TableNode $formData = null) {
		$lastNotifications = end($this->notificationIds);
		if ($notification === 'first') {
			$notificationId = reset($lastNotifications);
		} else { /* if ($notification === 'last')*/
			$notificationId = end($lastNotifications);
		}

		$this->sendingTo('GET', '/apps/notifications/api/' . $api . '/notifications/' . $notificationId . '?format=json');
		$this->assertStatusCode($this->response, 200);
		$response = $this->getArrayOfNotificationsResponded($this->response);

		foreach ($formData->getRowsHash() as $key => $value) {
			Assert::assertArrayHasKey($key, $response);
			Assert::assertEquals($value, $response[$key]);
		}
	}

	#[Then('/^delete (first|last|same|faulty) notification on (v\d+)$/')]
	public function deleteNotification(string $toDelete, string $api) {
		Assert::assertNotEmpty($this->notificationIds);
		$lastNotificationIds = end($this->notificationIds);
		if ($toDelete === 'first') {
			$this->deletedNotification = end($lastNotificationIds);
		} elseif ($toDelete === 'last') {
			$this->deletedNotification = reset($lastNotificationIds);
		} elseif ($toDelete === 'faulty') {
			$this->deletedNotification = 'faulty';
		}
		$this->sendingTo('DELETE', '/apps/notifications/api/' . $api . '/notifications/' . $this->deletedNotification);
	}

	#[Then('/^delete all notifications on (v\d+)$/')]
	public function deleteAllNotification($api) {
		Assert::assertNotEmpty($this->notificationIds);
		$this->sendingTo('DELETE', '/apps/notifications/api/' . $api . '/notifications');
	}

	#[Then('/^user "([^"]*)" unregisters from push notifications/')]
	public function unregisterForPushNotifications(string $user) {
		$currentUser = $this->currentUser;
		$this->setCurrentUser($user);
		$this->sendingToWith('DELETE', '/apps/notifications/api/v2/push?format=json');
		$this->setCurrentUser($currentUser);
	}

	#[Then('/^user "([^"]*)" registers for push notifications with$/')]
	public function registerForPushNotifications(string $user, TableNode $formData) {
		$data = $formData->getRowsHash();

		if ($data['devicePublicKey'] === 'VALID_KEY') {
			$config = [
				'digest_alg' => 'sha512',
				'private_key_bits' => 2048,
				'private_key_type' => OPENSSL_KEYTYPE_RSA,
			];
			$this->deviceKey = openssl_pkey_new($config);
			$keyDetails = openssl_pkey_get_details($this->deviceKey);
			$publicKey = $keyDetails['key'];

			$data['devicePublicKey'] = $publicKey;
		}

		$currentUser = $this->currentUser;
		$this->setCurrentUser($user);
		$this->sendingToWith('POST', '/apps/notifications/api/v2/push?format=json', $data);
		$this->setCurrentUser($currentUser);
	}

	#[Then('/^can validate the response and (skip verifying|verify) signature$/')]
	public function validateResponseAndSignature(string $verify): void {
		$response = $this->getArrayOfNotificationsResponded($this->response);

		Assert::assertStringStartsWith('-----BEGIN PUBLIC KEY-----' . "\n", $response['publicKey']);
		Assert::assertStringEndsWith('-----END PUBLIC KEY-----' . "\n", $response['publicKey']);
		Assert::assertNotEmpty($response['deviceIdentifier'], 'Device identifier should not be empty');
		Assert::assertNotEmpty($response['signature'], 'Signature should not be empty');

		if ($verify === 'verify') {
			$result = openssl_verify($response['deviceIdentifier'], base64_decode($response['signature']), $response['publicKey'], OPENSSL_ALGO_SHA512);
			Assert::assertEquals(true, $result, 'Failed to verify the signature');
		} else {
			/**
			 * For some weird reason the push proxy's golang code needs the signature
			 * of the deviceIdentifier before the sha512 hashing. Assumption is that
			 * openssl_sign already does the sha512 internally.
			 * The problem is we can not revert the sha512 of the deviceIdentifier
			 */
			var_dump("\n\nEnjoy with care, signature was not verified!\n\n");
		}
	}

	#[Then('/^user "([^"]*)" creates an app password$/')]
	public function createAppPassword(string $user) {
		$currentUser = $this->currentUser;
		$this->setCurrentUser($user);
		$this->sendingToWith('GET', '/core/getapppassword?format=json');
		$this->setCurrentUser($currentUser);

		$response = $this->getArrayOfNotificationsResponded($this->response);
		Assert::assertNotEquals('', $response['apppassword']);
		$this->appPasswords[$user] = $response['apppassword'];
	}

	#[Then('/^user "([^"]*)" forgets the app password$/')]
	public function removeAppPassword(string $user) {
		unset($this->appPasswords[$user]);
	}

	#[Then('/^error "([^"]*)" is expected with status code ([0-9]*)$/')]
	public function expectedErrorOnLastRequest(string $error, int $statusCode) {
		$this->assertStatusCode($this->response, $statusCode);
		$response = $this->getArrayOfNotificationsResponded($this->response);

		Assert::assertEquals($error, $response['message']);
	}

	#[Then('/^status code is ([0-9]*)$/')]
	public function isStatusCode(int $statusCode) {
		$this->assertStatusCode($this->response, $statusCode);
	}

	#[Given('/^webpush is enabled$/')]
	public function enableWebPush() {
		$this->runOcc(['config:app:set', 'notifications', 'webpush_enabled', '--value=1']);
	}

	#[Given('/^webpush is disabled$/')]
	public function disableWebPush() {
		$this->runOcc(['config:app:set', 'notifications', 'webpush_enabled', '--value=0']);
	}

	/**
	 * Generate a valid P256dh key and auth secret for WebPush testing
	 */
	protected function generateWebPushKeys(): void {
		// Generate EC P-256 key pair
		$key = openssl_pkey_new([
			'curve_name' => 'prime256v1',
			'private_key_type' => OPENSSL_KEYTYPE_EC,
		]);
		$details = openssl_pkey_get_details($key);
		// Build uncompressed public key: 0x04 || x || y (65 bytes)
		$x = str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT);
		$y = str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
		$uncompressed = "\x04" . $x . $y;
		$this->webPushPublicKey = rtrim(strtr(base64_encode($uncompressed), '+/', '-_'), '=');

		// Generate 16 bytes of random auth
		$authBytes = random_bytes(16);
		$this->webPushAuth = rtrim(strtr(base64_encode($authBytes), '+/', '-_'), '=');
	}

	#[When('/^user "([^"]*)" fetches the VAPID public key$/')]
	public function fetchVapidPublicKey(string $user) {
		$currentUser = $this->currentUser;
		$this->setCurrentUser($user);
		$this->sendingToWith('GET', '/apps/notifications/api/v2/webpush/vapid?format=json');
		$this->setCurrentUser($currentUser);
	}

	#[Then('/^the VAPID key is not empty$/')]
	public function vapidKeyIsNotEmpty() {
		$response = $this->getDataFromOCSResponse($this->response);
		Assert::assertNotEmpty($response['vapid'], 'VAPID public key should not be empty');
	}

	#[Given('/^user "([^"]*)" registers for webpush with$/')]
	public function registerForWebPush(string $user, TableNode $formData) {
		$data = $formData->getRowsHash();

		if ($data['uaPublicKey'] === 'VALID_KEY' || $data['auth'] === 'VALID_AUTH') {
			$this->generateWebPushKeys();
		}
		if ($data['uaPublicKey'] === 'VALID_KEY') {
			$data['uaPublicKey'] = $this->webPushPublicKey;
		}
		if ($data['auth'] === 'VALID_AUTH') {
			$data['auth'] = $this->webPushAuth;
		}

		$currentUser = $this->currentUser;
		$this->setCurrentUser($user);
		$this->sendingToWith('POST', '/apps/notifications/api/v2/webpush?format=json', $data);
		$this->setCurrentUser($currentUser);
	}

	#[Given('/^user "([^"]*)" activates webpush with the activation token$/')]
	public function activateWebPushWithDbToken(string $user) {
		// Fetch activation token from the database via the testing helper
		$currentUser = $this->currentUser;
		$this->setCurrentUser('admin');
		$this->sendingToWith('GET', '/apps/notificationsintegrationtesting/webpush/activation-token?format=json&userId=' . $user);
		$this->setCurrentUser($user);

		$this->assertStatusCode($this->response, 200);
		$jsonBody = json_decode($this->response->getBody()->getContents(), true);
		Assert::assertIsArray($jsonBody, 'Response body should be valid JSON');
		$activationToken = $jsonBody['ocs']['data']['activationToken'] ?? '';
		Assert::assertNotEmpty($activationToken, 'Activation token should not be empty');

		$this->sendingToWith('POST', '/apps/notifications/api/v2/webpush/activate?format=json', [
			'activationToken' => $activationToken,
		]);
		$this->setCurrentUser($currentUser);
	}

	#[Given('/^user "([^"]*)" activates webpush with token "([^"]*)"$/')]
	public function activateWebPushWithToken(string $user, string $token) {
		$currentUser = $this->currentUser;
		$this->setCurrentUser($user);
		$this->sendingToWith('POST', '/apps/notifications/api/v2/webpush/activate?format=json', [
			'activationToken' => $token,
		]);
		$this->setCurrentUser($currentUser);
	}

	#[Given('/^user "([^"]*)" removes webpush subscription$/')]
	public function removeWebPushSubscription(string $user) {
		$currentUser = $this->currentUser;
		$this->setCurrentUser($user);
		$this->sendingToWith('DELETE', '/apps/notifications/api/v2/webpush?format=json');
		$this->setCurrentUser($currentUser);
	}

	#[BeforeScenario]
	#[AfterScenario]
	public function clearNotifications() {
		$response = $this->setTestingValue('DELETE', 'apps/notificationsintegrationtesting', null);
		$this->assertStatusCode($response, 200);
	}

	protected function setTestingValue(string $verb, string $url, ?TableNode $body = null) {
		$fullUrl = $this->baseUrl . 'ocs/v2.php/' . $url;
		$client = new Client();
		$options = [
			'auth' => ['admin', 'admin'],
		];
		if ($body instanceof TableNode) {
			$fd = $body->getRowsHash();
			$options['form_params'] = $fd;
		} elseif (is_array($body)) {
			$options['form_params'] = $body;
		}

		try {
			return $client->{$verb}($fullUrl, $options);
		} catch (ClientException $ex) {
			return $ex->getResponse();
		}
	}

	/*
	 * User management
	 */

	#[Given('/^as user "([^"]*)"$/')]
	public function setCurrentUser(string $user) {
		$this->currentUser = $user;
	}

	#[Given('/^user "([^"]*)" exists$/')]
	public function assureUserExists(string $user) {
		try {
			$this->userExists($user);
		} catch (ClientException) {
			$this->createUser($user);
		}
		$response = $this->userExists($user);
		$this->assertStatusCode($response, 200);
	}

	private function userExists(string $user): ResponseInterface {
		$client = new Client();
		$options = [
			'auth' => ['admin', 'admin'],
			'headers' => [
				'OCS-APIREQUEST' => 'true',
			],
		];
		return $client->get($this->baseUrl . 'ocs/v2.php/cloud/users/' . $user, $options);
	}

	private function createUser(string $user) {
		$previous_user = $this->currentUser;
		$this->currentUser = 'admin';

		$userProvisioningUrl = $this->baseUrl . 'ocs/v2.php/cloud/users';
		$client = new Client();
		$options = [
			'auth' => ['admin', 'admin'],
			'form_params' => [
				'userid' => $user,
				'password' => '123456'
			],
			'headers' => [
				'OCS-APIREQUEST' => 'true',
			],
		];
		$client->post($userProvisioningUrl, $options);

		//Quick hack to login once with the current user
		$options2 = [
			'auth' => [$user, '123456'],
			'headers' => [
				'OCS-APIREQUEST' => 'true',
			],
		];
		$client->get($userProvisioningUrl . '/' . $user, $options2);

		$this->currentUser = $previous_user;
	}

	/*
	 * Requests
	 */

	#[When('/^sending "([^"]*)" to "([^"]*)"$/')]
	public function sendingTo(string $verb, string $url) {
		$this->sendingToWith($verb, $url, null);
	}

	/**
	 * @param TableNode|array|null $body
	 */
	#[When('/^sending "([^"]*)" to "([^"]*)" with$/')]
	public function sendingToWith(string $verb, string $url, $body = null, array $headers = []) {
		$fullUrl = $this->baseUrl . 'ocs/v2.php' . $url;
		$client = new Client();
		$options = [];
		if (isset($this->appPasswords[$this->currentUser])) {
			$options['auth'] = [$this->currentUser, $this->appPasswords[$this->currentUser]];
		} elseif ($this->currentUser === 'admin') {
			$options['auth'] = [$this->currentUser, 'admin'];
		} else {
			$options['auth'] = [$this->currentUser, '123456'];
		}
		if ($body instanceof TableNode) {
			$fd = $body->getRowsHash();
			$options['form_params'] = $fd;
		} elseif (is_array($body)) {
			$options['form_params'] = $body;
		}

		$options['headers'] = array_merge($headers, [
			'OCS-APIREQUEST' => 'true',
		]);

		try {
			$this->response = $client->request($verb, $fullUrl, $options);
		} catch (ClientException $ex) {
			$this->response = $ex->getResponse();
		}
	}

	protected function assertStatusCode(ResponseInterface $response, int $statusCode) {
		Assert::assertEquals($statusCode, $response->getStatusCode());
	}
}
