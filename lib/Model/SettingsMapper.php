<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021 Joas Schilling <coding@schilljs.com>
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

namespace OCA\Notifications\Model;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception as DBException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @method Settings mapRowToEntity(array $row)
 * @method Settings findEntity(IQueryBuilder $query)
 * @method Settings[] findEntities(IQueryBuilder $query)
 */
class SettingsMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'notifications_settings', Settings::class);
	}

	/**
	 * @param string $userId
	 * @return Settings
	 * @throws DBException
	 * @throws MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function getSettingsByUser(string $userId): Settings {
		$query = $this->db->getQueryBuilder();

		$query->select('*')
			->from($this->getTableName())
			->where($query->expr()->eq('user_id', $query->createNamedParameter($userId)));

		return $this->findEntity($query);
	}

	/**
	 * @param int $limit
	 * @return Settings[]
	 * @throws DBException
	 */
	public function getUsersByNextSendTime(int $limit): array {
		$query = $this->db->getQueryBuilder();

		$query->select('*')
			->from($this->getTableName())
			->where($query->expr()->gt('next_send_time', $query->createNamedParameter(0)))
			->orderBy('next_send_time', 'ASC')
			->setMaxResults($limit);

		return $this->findEntities($query);
	}

	public function createSettingsFromRow(array $row): Settings {
		return $this->mapRowToEntity([
			'id' => $row['id'],
			'user_id' => (string) $row['user_id'],
			'batch_time' => (int) $row['batch_time'],
			'last_send_id' => (int) $row['last_send_id'],
			'next_send_time' => (int) $row['next_send_time'],
		]);
	}
}
