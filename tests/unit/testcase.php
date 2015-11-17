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

namespace OCA\Notifications\Tests;

abstract class TestCase extends \Test\TestCase {
	/** @var array */
	protected $services = [];

	/**
	 * @param string $name
	 * @param mixed $newService
	 * @return bool
	 */
	public function overwriteService($name, $newService) {
		if (isset($this->services[$name])) {
			return false;
		}

		$this->services[$name] = \OC::$server->query($name);
		\OC::$server->registerService($name, function () use ($newService) {
			return $newService;
		});

		return true;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function restoreService($name) {
		if (isset($this->services[$name])) {
			$oldService = $this->services[$name];
			\OC::$server->registerService($name, function () use ($oldService) {
				return $oldService;
			});


			unset($this->services[$name]);
			return true;
		}

		return false;
	}
}
