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

use OC\Notification\INotification;
use OC\Notification\INotifier;
use OCP\IConfig;

class Notifier implements INotifier {

	/** @var IConfig */
	private $config;

	/**
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	/**
	 * @return bool
	 */
	protected function isDebugMode() {
		if ($this->config->getAppValue('notifications', 'debug', '') !== '' && $this->config->getAppValue('notifications', 'forceHasNotifiers', '') !== '') {
			return $this->config->getAppValue('notifications', 'forceHasNotifiers') === 'true';
		}
		return false;
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 * @return INotification
	 * @throws \InvalidArgumentException When the notification was not prepared by a notifier
	 */
	public function prepare(INotification $notification, $languageCode) {
		if ($this->isDebugMode() && $notification->getApp() === 'testing') {
			$notification->setParsedSubject($notification->getSubject());
			$notification->setParsedMessage($notification->getMessage());
			return $notification;
		}

		throw new \InvalidArgumentException();
	}
}
