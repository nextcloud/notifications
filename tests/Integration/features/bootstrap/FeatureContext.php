<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
require __DIR__ . '/../../vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

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

	/** @var \GuzzleHttp\Cookie\CookieJar */
	private $cookieJar;

	/** @var string */
	protected $baseUrl;

	/**
	 * FeatureContext constructor.
	 */
	public function __construct() {
		$this->cookieJar = new \GuzzleHttp\Cookie\CookieJar();
		$this->baseUrl = getenv('TEST_SERVER_URL');
	}

	/**
	 * @Given /^user "([^"]*)" has notifications$/
	 *
	 * @param string $user
	 */
	public function hasNotifications($user) {
		if ($user === 'test1') {
			$response = $this->setTestingValue('POST', 'apps/notificationsintegrationtesting/notifications', null);
			$this->assertStatusCode($response, 200);
		}
	}

	/**
	 * @Given /^user "([^"]*)" receives notification with$/
	 *
	 * @param string $user
	 * @param \Behat\Gherkin\Node\TableNode|null $formData
	 */
	public function receiveNotification($user, \Behat\Gherkin\Node\TableNode $formData) {
		if ($user === 'test1') {
			$response = $this->setTestingValue('POST', 'apps/notificationsintegrationtesting/notifications', $formData);
			$this->assertStatusCode($response, 200);
		}
	}

	/**
	 * @Then /^list of notifications has (\d+) entries$/
	 *
	 * @param int $numNotifications
	 */
	public function checkNumNotifications($numNotifications) {
		$notifications = $this->getArrayOfNotificationsResponded($this->response);
		PHPUnit_Framework_Assert::assertCount((int) $numNotifications, $notifications);

		$notificationIds = [];
		foreach ($notifications as $notification) {
			$notificationIds[] = (int) $notification['notification_id'];
		}

		$this->notificationIds[] = $notificationIds;
	}

	/**
	 * Parses the xml answer to get the array of users returned.
	 * @param ResponseInterface $response
	 * @return array
	 */
	protected function getArrayOfNotificationsResponded(ResponseInterface $response) {
		$jsonResponse = json_decode($response->getBody()->getContents(), 1);
		return $jsonResponse['ocs']['data'];
	}

	/**
	 * @Then /^user "([^"]*)" has (\d+) notifications(| missing the last one| missing the first one)$/
	 *
	 * @param string $user
	 * @param int $numNotifications
	 * @param string $missingLast
	 */
	public function userNumNotifications($user, $numNotifications, $missingLast) {
		if ($user === 'test1') {
			$this->sendingTo('GET', '/apps/notifications/api/v1/notifications?format=json');
			$this->assertStatusCode($this->response, 200);

			$previousNotificationIds = [];
			if ($missingLast) {
				PHPUnit_Framework_Assert::assertNotEmpty($this->notificationIds);
				$previousNotificationIds = end($this->notificationIds);
			}

			$this->checkNumNotifications((int) $numNotifications);

			if ($missingLast) {
				$now = end($this->notificationIds);
				if ($missingLast === ' missing the last one') {
					array_unshift($now, $this->deletedNotification);
				} else {
					$now[] = $this->deletedNotification;
				}

				PHPUnit_Framework_Assert::assertEquals($previousNotificationIds, $now);
			}

		}
	}

	/**
	 * @Then /^(first|last) notification matches$/
	 *
	 * @param string $notification
	 * @param \Behat\Gherkin\Node\TableNode|null $formData
	 */
	public function matchNotification($notification, $formData) {
		$lastNotifications = end($this->notificationIds);
		if ($notification === 'first') {
			$notificationId = reset($lastNotifications);
		} else/* if ($notification === 'last')*/ {
			$notificationId = end($lastNotifications);
		}

		$this->sendingTo('GET', '/apps/notifications/api/v1/notifications/' . $notificationId . '?format=json');
		$this->assertStatusCode($this->response, 200);
		$response = json_decode($this->response->getBody()->getContents(), true);

		foreach ($formData->getRowsHash() as $key => $value) {
			PHPUnit_Framework_Assert::assertArrayHasKey($key, $response['ocs']['data']);
			PHPUnit_Framework_Assert::assertEquals($value, $response['ocs']['data'][$key]);
		}
	}

	/**
	 * @Then /^delete (first|last|same) notification$/
	 *
	 * @param string $firstOrLast
	 */
	public function deleteNotification($firstOrLast) {
		PHPUnit_Framework_Assert::assertNotEmpty($this->notificationIds);
		$lastNotificationIds = end($this->notificationIds);
		if ($firstOrLast === 'first') {
			$this->deletedNotification = end($lastNotificationIds);
		} else if ($firstOrLast === 'last') {
			$this->deletedNotification = reset($lastNotificationIds);
		}
		$this->sendingTo('DELETE', '/apps/notifications/api/v1/notifications/' . $this->deletedNotification);
	}

	/**
	 * @Then /^status code is ([0-9]*)$/
	 *
	 * @param int $statusCode
	 */
	public function isStatusCode($statusCode) {
		$this->assertStatusCode($this->response, $statusCode);
	}

	/**
	 * @BeforeScenario
	 * @AfterScenario
	 */
	public function clearNotifications() {
		$response = $this->setTestingValue('DELETE', 'apps/notificationsintegrationtesting', null);
		$this->assertStatusCode($response, 200);
	}

	/**
	 * @param $verb
	 * @param $url
	 * @param $body
	 * @return \GuzzleHttp\Message\FutureResponse|ResponseInterface|null
	 */
	protected function setTestingValue($verb, $url, $body) {
		$fullUrl = $this->baseUrl . 'ocs/v2.php/' . $url;
		$client = new Client();
		$options = [
			'auth' => ['admin', 'admin'],
		];
		if ($body instanceof \Behat\Gherkin\Node\TableNode) {
			$fd = $body->getRowsHash();
			$options['body'] = $fd;
		}

		try {
			return $client->send($client->createRequest($verb, $fullUrl, $options));
		} catch (\GuzzleHttp\Exception\ClientException $ex) {
			return $ex->getResponse();
		}
	}

	/*
	 * User management
	 */

	/**
	 * @Given /^as user "([^"]*)"$/
	 * @param string $user
	 */
	public function setCurrentUser($user) {
		$this->currentUser = $user;
	}

	/**
	 * @Given /^user "([^"]*)" exists$/
	 * @param string $user
	 */
	public function assureUserExists($user) {
		try {
			$this->userExists($user);
		} catch (\GuzzleHttp\Exception\ClientException $ex) {
			$this->createUser($user);
		}
		$response = $this->userExists($user);
		$this->assertStatusCode($response, 200);
	}

	private function userExists($user) {
		$client = new Client();
		$options = [
			'auth' => ['admin', 'admin'],
			'headers' => [
				'OCS-APIREQUEST' => 'true',
			],
		];
		return $client->get($this->baseUrl . 'ocs/v2.php/cloud/users/' . $user, $options);
	}

	private function createUser($user) {
		$previous_user = $this->currentUser;
		$this->currentUser = "admin";

		$userProvisioningUrl = $this->baseUrl . 'ocs/v2.php/cloud/users';
		$client = new Client();
		$options = [
			'auth' => ['admin', 'admin'],
			'body' => [
				'userid' => $user,
				'password' => '123456'
			],
			'headers' => [
				'OCS-APIREQUEST' => 'true',
			],
		];
		$client->send($client->createRequest('POST', $userProvisioningUrl, $options));

		//Quick hack to login once with the current user
		$options2 = [
			'auth' => [$user, '123456'],
			'headers' => [
				'OCS-APIREQUEST' => 'true',
			],
		];
		$client->send($client->createRequest('GET', $userProvisioningUrl . '/' . $user, $options2));

		$this->currentUser = $previous_user;
	}

	/*
	 * Requests
	 */

	/**
	 * @When /^sending "([^"]*)" to "([^"]*)"$/
	 * @param string $verb
	 * @param string $url
	 */
	public function sendingTo($verb, $url) {
		$this->sendingToWith($verb, $url, null);
	}

	/**
	 * @When /^sending "([^"]*)" to "([^"]*)" with$/
	 * @param string $verb
	 * @param string $url
	 * @param \Behat\Gherkin\Node\TableNode $body
	 */
	public function sendingToWith($verb, $url, $body) {
		$fullUrl = $this->baseUrl . 'ocs/v2.php' . $url;
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = ['admin', 'admin'];
		} else {
			$options['auth'] = [$this->currentUser, '123456'];
		}
		if ($body instanceof \Behat\Gherkin\Node\TableNode) {
			$fd = $body->getRowsHash();
			$options['body'] = $fd;
		}

		$options['headers'] = [
			'OCS-APIREQUEST' => 'true',
		];

		try {
			$this->response = $client->send($client->createRequest($verb, $fullUrl, $options));
		} catch (\GuzzleHttp\Exception\ClientException $ex) {
			$this->response = $ex->getResponse();
		}
	}

	/**
	 * Parses the xml answer to get ocs response which doesn't match with
	 * http one in v1 of the api.
	 * @param ResponseInterface $response
	 * @return string
	 */
	private function getOCSResponse($response) {
		return $response->xml()->meta[0]->statuscode;
	}

	/**
	 * @param ResponseInterface $response
	 * @param int $statusCode
	 */
	protected function assertStatusCode(ResponseInterface $response, $statusCode) {
		PHPUnit_Framework_Assert::assertEquals($statusCode, $response->getStatusCode());
		//PHPUnit_Framework_Assert::assertEquals($statusCode, $this->getOCSResponse($response));
	}
}
