<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
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

namespace OCA\Notifications\Tests\AppInfo;

use OCA\Notifications\Tests\TestCase;

class AppTest extends TestCase {
	/** @var \OC\Notification\IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $manager;
	/** @var \OCP\IRequest|\PHPUnit_Framework_MockObject_MockObject */
	protected $request;

	protected function setUp() {
		parent::setUp();

		$this->manager = $this->getMockBuilder('OC\Notification\IManager')
			->disableOriginalConstructor()
			->getMock();

		$this->request = $this->getMockBuilder('OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();

		$this->overwriteService('NotificationManager', $this->manager);
		$this->overwriteService('Request', $this->request);
	}

	protected function tearDown() {
		$this->restoreService('NotificationManager');
		$this->restoreService('Request');

		parent::tearDown();
	}

	public function testRegisterApp() {
		$this->manager->expects($this->once())
			->method('registerApp')
			->willReturnCallback(function($closure) {
				$this->assertInstanceOf('\Closure', $closure);
				$navigation = $closure();
				$this->assertInstanceOf('\OCA\Notifications\App', $navigation);
			});

		include(__DIR__ . '/../../appinfo/app.php');
	}

	public function dataLoadingJSAndCSS() {
		return [
			['/index.php', '/apps/files', true],
			['/remote.php', '/webdav', false],
			['/index.php', '/s/1234567890123', false],
		];
	}

	/**
	 * @dataProvider dataLoadingJSAndCSS
	 * @param string $scriptName
	 * @param string $pathInfo
	 * @param bool $scriptsAdded
	 */
	public function testLoadingJSAndCSS($scriptName, $pathInfo, $scriptsAdded) {
		$this->request->expects($this->any())
			->method('getScriptName')
			->willReturn($scriptName);
		$this->request->expects($this->any())
			->method('getPathInfo')
			->willReturn($pathInfo);

		\OC_Util::$scripts = [];
		\OC_Util::$styles = [];

		include(__DIR__ . '/../../appinfo/app.php');

		if ($scriptsAdded) {
			$this->assertNotEmpty(\OC_Util::$scripts);
			$this->assertNotEmpty(\OC_Util::$styles);
		} else {
			$this->assertEmpty(\OC_Util::$scripts);
			$this->assertEmpty(\OC_Util::$styles);
		}
	}
}
