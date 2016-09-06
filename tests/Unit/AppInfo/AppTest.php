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
use OCA\Notifications\Tests\Unit\TestCase;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IManager;

/**
 * Class AppTest
 *
 * @group DB
 * @package OCA\Notifications\Tests\AppInfo
 */
class AppTest extends TestCase {
	/** @var IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $manager;
	/** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
	protected $request;
	/** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
	protected $session;

	protected function setUp() {
		parent::setUp();

		$this->manager = $this->getMockBuilder(IManager::class)
			->getMock();

		$this->request = $this->getMockBuilder(IRequest::class)
			->getMock();

		$this->session = $this->getMockBuilder(IUserSession::class)
			->getMock();

		$this->overwriteService('NotificationManager', $this->manager);
		$this->overwriteService('Request', $this->request);
		$this->overwriteService('UserSession', $this->session);
	}

	protected function tearDown() {
		$this->restoreService('NotificationManager');
		$this->restoreService('Request');
		$this->restoreService('UserSession');

		parent::tearDown();
	}

	public function testRegisterApp() {
		$this->manager->expects($this->once())
			->method('registerApp')
			->willReturnCallback(function($closure) {
				$this->assertInstanceOf(\Closure::class, $closure);
				$navigation = $closure();
				$this->assertInstanceOf(App::class, $navigation);
			});

		include(__DIR__ . '/../../../appinfo/app.php');
	}

	public function dataLoadingJSAndCSS() {
		$user = $this->getMockBuilder(IUser::class)
			->getMock();

		return [
			['/index.php', '/apps/files', $user, true],
			['/index.php', '/apps/files', null, false],
			['/remote.php', '/webdav', $user, false],
			['/index.php', '/s/1234567890123', $user, false],
			['/index.php', '/login/selectchallenge', $user, false],
		];
	}

	/**
	 * @dataProvider dataLoadingJSAndCSS
	 * @param string $scriptName
	 * @param string $pathInfo
	 * @param IUser|null $user
	 * @param bool $scriptsAdded
	 */
	public function testLoadingJSAndCSS($scriptName, $pathInfo, $user, $scriptsAdded) {
		$this->request->expects($this->any())
			->method('getScriptName')
			->willReturn($scriptName);
		$this->request->expects($this->any())
			->method('getPathInfo')
			->willReturn($pathInfo);
		$this->session->expects($this->once())
			->method('getUser')
			->willReturn($user);

		\OC_Util::$scripts = [];
		\OC_Util::$styles = [];

		include(__DIR__ . '/../../../appinfo/app.php');

		if ($scriptsAdded) {
			$this->assertNotEmpty(\OC_Util::$scripts);
			$this->assertNotEmpty(\OC_Util::$styles);
		} else {
			$this->assertEmpty(\OC_Util::$scripts);
			$this->assertEmpty(\OC_Util::$styles);
		}
	}
}
