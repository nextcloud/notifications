<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021, Julien Barnoin <julien@barnoin.com>
 *
 * @author Julien Barnoin <julien@barnoin.com>
 *
 * @license AGPL-3.0-or-later
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

	/** @var MailNotifications */
	protected $mailNotifications;
	/** @var bool */
	protected $isCLI;

	public function __construct(ITimeFactory $timeFactory,
								MailNotifications $mailNotifications,
								bool $isCLI) {
		parent::__construct($timeFactory);

		$this->mailNotifications = $mailNotifications;
		$this->isCLI = $isCLI;
	}

	protected function run($argument): void {
		$time = $this->time->getTime();
		$batchSize = $this->isCLI ? MailNotifications::BATCH_SIZE_CLI : MailNotifications::BATCH_SIZE_WEB;
		$this->mailNotifications->sendEmails($batchSize, $time);
	}
}
