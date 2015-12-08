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

namespace OCA\Notifications\Tests\Integration;

use OC\Notification\IManager;
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IRequest;

class Controller extends \OCP\AppFramework\Controller {

	/** @var IConfig */
	private $config;

	/** @var IManager */
	private $manager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param IManager $manager
	 */
	public function __construct($appName, IRequest $request, IConfig $config, IManager $manager) {
		parent::__construct($appName, $request);

		$this->config = $config;
		$this->manager = $manager;
	}

	/**
	 * @NoCSRFRequired
	 *
	 * @return \OC_OCS_Result
	 */
	public function fillNotifiers() {
		if (!$this->config->getAppValue('notifications', 'debug')) {
			return new \OC_OCS_Result(null, Http::STATUS_FORBIDDEN);
		}

		$this->config->setAppValue('notifications', 'forceHasNotifiers', 'true');
		return new \OC_OCS_Result();
	}

	/**
	 * @NoCSRFRequired
	 *
	 * @return \OC_OCS_Result
	 */
	public function clearNotifiers() {
		if (!$this->config->getAppValue('notifications', 'debug')) {
			return new \OC_OCS_Result(null, Http::STATUS_FORBIDDEN);
		}

		$this->config->setAppValue('notifications', 'forceHasNotifiers', 'false');
		return new \OC_OCS_Result();
	}

	/**
	 * @NoCSRFRequired
	 *
	 * @return \OC_OCS_Result
	 */
	public function reset() {
		if (!$this->config->getAppValue('notifications', 'debug')) {
			return new \OC_OCS_Result(null, Http::STATUS_FORBIDDEN);
		}

		$this->config->deleteAppValue('notifications', 'forceHasNotifiers');
		return new \OC_OCS_Result();
	}
}
