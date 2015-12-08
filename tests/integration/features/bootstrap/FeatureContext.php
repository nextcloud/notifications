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
}
