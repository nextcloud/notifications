<?php

require __DIR__ . '/../../../../../../build/integration/features/bootstrap/BasicStructure.php';
require __DIR__ . '/../../../../../../build/integration/features/bootstrap/Provisioning.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext {

	use BasicStructure;
	use Provisioning;

	/**
	 * @Given /^list of notifiers (is|is not) empty$/
	 */
	public function hasNotifiers($noNotifiers) {
		if ($noNotifiers === 'is') {
			$response = $this->setTestingValue('DELETE', 'apps/notifications/testing/notifiers', null);
			PHPUnit_Framework_Assert::assertEquals(200, $response->getStatusCode());
			PHPUnit_Framework_Assert::assertEquals(200, (int) $this->getOCSResponse($response));
		} else {
			$response = $this->setTestingValue('POST', 'apps/notifications/testing/notifiers', null);
			PHPUnit_Framework_Assert::assertEquals(200, $response->getStatusCode());
			PHPUnit_Framework_Assert::assertEquals(200, (int) $this->getOCSResponse($response));
		}
	}

	/**
	 * @Given /^user "([^"]*)" has notifications$/
	 */
	public function hasNotifications($user) {
		if ($user === 'test1') {
			$response = $this->setTestingValue('POST', 'apps/notifications/testing/notifications', null);
			PHPUnit_Framework_Assert::assertEquals(200, $response->getStatusCode());
			PHPUnit_Framework_Assert::assertEquals(200, (int) $this->getOCSResponse($response));
		}
	}

	/**
	 * @Then /^list of notifications has (\d+) entries$/
	 */
	public function checkNumNotifications($numNotifications) {
		$notifications = $this->getArrayOfNotificationsResponded($this->response);
		PHPUnit_Framework_Assert::assertCount((int) $numNotifications, $notifications);
	}

	/**
	 * Parses the xml answer to get the array of users returned.
	 * @param ResponseInterface $resp
	 * @return array
	 */
	public function getArrayOfNotificationsResponded($resp) {
		$jsonResponse = json_decode($resp->getBody()->getContents(), 1);
		return $jsonResponse['ocs']['data'];
	}

	/**
	 * @BeforeSuite
	 */
	public static function addFilesToSkeleton() {
		// The path to the skeleton files does not match, and we don't need them
	}

	/**
	 * @AfterSuite
	 */
	public static function removeFilesFromSkeleton() {
		// The path to the skeleton files does not match, and we don't need them
	}

	/**
	 * @AfterScenario
	 */
	public function removeDebugConfigs() {
		$response = $this->setTestingValue('DELETE', 'apps/notifications/testing', null);
		PHPUnit_Framework_Assert::assertEquals(200, $response->getStatusCode());
		PHPUnit_Framework_Assert::assertEquals(200, (int) $this->getOCSResponse($response));
	}

	protected function setTestingValue($verb, $url, $body) {
		$fullUrl = $this->baseUrl . "v2.php/" . $url;
		$client = new Client();
		$options = [
			'auth' => $this->adminUser,
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
}
