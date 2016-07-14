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

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Tests\Unit\TestCase;

/**
 * Class ApplicationTest
 *
 * @group DB
 * @package OCA\Notifications\Tests\AppInfo
 */
class ApplicationTest extends TestCase {
	/** @var \OCA\Notifications\AppInfo\Application */
	protected $app;

	/** @var \OCP\AppFramework\IAppContainer */
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
			array('EndpointController', 'OCA\Notifications\Controller\EndpointController'),
			array('Capabilities', 'OCA\Notifications\Capabilities'),
		);
	}

	/**
	 * @dataProvider dataContainerQuery
	 * @param string $service
	 * @param string $expected
	 */
	public function testContainerQuery($service, $expected) {
		$this->assertTrue($this->container->query($service) instanceof $expected);
	}

	public function dataGetCurrentUser() {
		$user = $this->getMockBuilder('OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->any())
			->method('getUID')
			->willReturn('uid');

		return [
			[$user, 'uid'],
			[null, ''],
		];
	}

	/**
	 * @dataProvider dataGetCurrentUser
	 * @param mixed $user
	 * @param string $expected
	 */
	public function testGetCurrentUser($user, $expected) {
		$session = $this->getMockBuilder('OCP\IUserSession')
			->disableOriginalConstructor()
			->getMock();
		$session->expects($this->any())
			->method('getUser')
			->willReturn($user);

		$this->assertSame($expected, $this->invokePrivate($this->app, 'getCurrentUser', [$session]));
	}
}
