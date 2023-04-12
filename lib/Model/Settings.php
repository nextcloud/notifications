<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021, Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OCA\Notifications\Model;

use OCP\AppFramework\Db\Entity;

/**
 *
 * @method void setUserId(string $userId)
 * @method string getUserId()
 * @method void setBatchTime(int $batchTime)
 * @method int getBatchTime()
 * @method void setLastSendId(int $lastSendId)
 * @method int getLastSendId()
 * @method void setNextSendTime(int $nextSendTime)
 * @method int getNextSendTime()
 */
class Settings extends Entity {
	public const EMAIL_SEND_WEEKLY = 4;
	public const EMAIL_SEND_DAILY = 3;
	public const EMAIL_SEND_3HOURLY = 2;
	public const EMAIL_SEND_HOURLY = 1;
	public const EMAIL_SEND_OFF = 0;

	/** @var string */
	protected $userId;
	/** @var int */
	protected $batchTime;
	/** @var int */
	protected $lastSendId;
	/** @var int */
	protected $nextSendTime;

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('batchTime', 'int');
		$this->addType('lastSendId', 'int');
		$this->addType('nextSendTime', 'int');
	}

	public function asArray(): array {
		return [
			'id' => $this->getId(),
			'user_id' => $this->getUserId(),
			'batch_time' => $this->getBatchTime(),
			'last_send_id' => $this->getLastSendId(),
			'next_send_time' => $this->getNextSendTime(),
		];
	}
}
