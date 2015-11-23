<?php

require __DIR__ . '/../../vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext {

	/** @var string */
	private $baseUrl = '';

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

	/** @var \Behat\Behat\Context\Context */
	private $coreContext;

	/** @BeforeScenario */
	public function gatherContexts(\Behat\Behat\Hook\Scope\BeforeScenarioScope $scope)
	{
		$environment = $scope->getEnvironment();

		$this->coreContext = $environment->getContext('CoreContext');
	}
}
