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

namespace OCA\Notifications\Tests\Unit\AppInfo;

use OCA\Notifications\App;
use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Capabilities;
use OCA\Notifications\Controller\EndpointController;
use OCA\Notifications\Handler;
use OCA\Notifications\Tests\Unit\TestCase;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\OCSController;
use OCP\Notification\IApp;

/**
 * Class ApplicationTest
 *
 * @group DB
 * @package OCA\Notifications\Tests\AppInfo
 */
class ApplicationTest extends TestCase {
	/** @var Application */
	protected $app;

	/** @var IAppContainer */
	protected $container;

	protected function setUp() {
		parent::setUp();
		$this->app = new Application();
		$this->container = $this->app->getContainer();
	}

	public function testContainerAppName() {
		$this->app = new Application();
		$this->assertEquals('notifications', $this->container->getAppName());
	}

	public function dataContainerQuery() {
		return array(
			// lib/
			array(App::class),
			array(App::class, IApp::class),
			array(Capabilities::class),
			array(Handler::class),

			// Controller/
			array('EndpointController', EndpointController::class),
			array('EndpointController', OCSController::class),
			array(EndpointController::class),
			array(EndpointController::class, OCSController::class),
		);
	}

	/**
	 * @dataProvider dataContainerQuery
	 * @param string $service
	 * @param string $expected
	 */
	public function testContainerQuery($service, $expected = null) {
		if ($expected === null) {
			$expected = $service;
		}
		$this->assertTrue($this->container->query($service) instanceof $expected);
	}
}
