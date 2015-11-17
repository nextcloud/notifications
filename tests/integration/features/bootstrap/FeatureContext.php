<?php

require __DIR__ . '/../../vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;


/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext {

	/** @var string */
	private $baseUrl = '';

	/** @var ResponseInterface */
	private $response = null;

	/** @var int */
	private $apiVersion = 1;

	/** @var string[]  */
	private $adminUser = ['admin', 'admin'];

	/** @var array */
	private $createdUsers = [];

	/**
	 * Initializes context.
	 *
	 * Every scenario gets its own context instance.
	 * You can also pass arbitrary arguments to the
	 * context constructor through behat.yml.
	 */
	public function __construct($baseUrl) {
		$this->baseUrl = $baseUrl;
		$testServerUrl = getenv('TEST_SERVER_URL');
		if ($testServerUrl !== false) {
			$this->baseUrl = $testServerUrl;
		}
	}

	/**
	 * @Given using api version :arg1
	 */
	public function usingApiVersion($version) {
		$this->apiVersion = $version;
	}

	/**
	 * @Given user :arg1 exists
	 */
	public function userExists($user) {
		try {
			$this->doesUserExist($user);
		} catch (\GuzzleHttp\Exception\ClientException $ex) {
			$this->creatingTheUser($user);
		}
		$this->doesUserExist($user);
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @When /^sending "([^"]*)" to "([^"]*)" with$/
	 * @param \Behat\Gherkin\Node\TableNode|null $formData
	 */
	public function sendingToWith($verb, $url, $body) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php" . $url;
		$client = new Client();
		$options = [];
		$options['auth'] = $this->adminUser;
		if ($body instanceof \Behat\Gherkin\Node\TableNode) {
			$fd = $body->getRowsHash();
			$options['body'] = $fd;
		}

		try {
			$this->response = $client->send($client->createRequest($verb, $fullUrl, $options));
		} catch (\GuzzleHttp\Exception\ClientException $ex) {
			$this->response = $ex->getResponse();
		}
	}

	/**
	 * @Then /^the OCS status code should be "([^"]*)"$/
	 */
	public function theOCSStatusCodeShouldBe($statusCode) {
		PHPUnit_Framework_Assert::assertEquals($statusCode, $this->getOCSResponse($this->response));
	}

	/**
	 * @Then /^the HTTP status code should be "([^"]*)"$/
	 */
	public function theHTTPStatusCodeShouldBe($statusCode) {
		PHPUnit_Framework_Assert::assertEquals($statusCode, $this->response->getStatusCode());
	}

	/**
	 * Parses the xml answer to get ocs response which doesn't match with
	 * http one in v1 of the api.
	 */
	private function getOCSResponse($response) {
		return $response->xml()->meta[0]->statuscode;
	}

	private function creatingTheUser($user) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users";
		$client = new Client();
		$options = [];
		$options['auth'] = $this->adminUser;

		$options['body'] = [
			'userid' => $user,
			'password' => '123456'
		];

		$this->response = $client->send($client->createRequest("POST", $fullUrl, $options));
		$this->createdUsers[$user] = $user;
	}

	public function doesUserExist($user){
		$fullUrl = $this->baseUrl . "v2.php/cloud/users/$user";
		$client = new Client();
		$options = [];
		$options['auth'] = $this->adminUser;

		$this->response = $client->get($fullUrl, $options);
	}

	public function deleteUser($user) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users/$user";
		$client = new Client();
		$options = [];
		$options['auth'] = $this->adminUser;

		$this->response = $client->send($client->createRequest("DELETE", $fullUrl, $options));
	}


	/**
	 * @BeforeScenario
	 * @AfterScenario
	 */
	public function cleanupUsers()
	{
		foreach($this->createdUsers as $user) {
			$this->deleteUser($user);
		}
	}

}
