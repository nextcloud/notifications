<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021 Julien Barnoin <julien@barnoin.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Notifications\BackgroundJob;

use OCP\BackgroundJob\TimedJob;
use OCA\Notifications\MailNotifications;
use OCP\AppFramework\Utility\ITimeFactory;

class SendNotificationMails extends TimedJob {

	/** @var ITimeFactory */
	protected $timeFactory;
	/** @var MailNotifications */
	protected $mailNotifications;

	public function __construct(ITimeFactory $timeFactory, MailNotifications $mailNotifications) {
		parent::__construct($timeFactory);

		// run every 15 min
		$this->setInterval(60 * 15);

		$this->timeFactory = $timeFactory;
		$this->mailNotifications = $mailNotifications;
	}

	protected function run($argument) {
		$this->mailNotifications->sendEmails();
	}
}
