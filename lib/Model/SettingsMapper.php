<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Model;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception as DBException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Settings>
 *
 * @method Settings mapRowToEntity(array $row)
 * @method Settings findEntity(IQueryBuilder $query)
 * @method list<Settings> findEntities(IQueryBuilder $query)
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
	 * @param string $userId
	 * @throws DBException
	 */
	public function deleteSettingsByUser(string $userId): void {
		$query = $this->db->getQueryBuilder();

		$query->delete($this->getTableName())
			->where($query->expr()->eq('user_id', $query->createNamedParameter($userId)));

		$query->executeStatement();
	}

	public function setBatchSettingForUser(string $userId, int $batchSetting): void {
		try {
			$settings = $this->getSettingsByUser($userId);
		} catch (DoesNotExistException) {
			$settings = new Settings();
			$settings->setUserId($userId);
			/** @var Settings $settings */
			$settings = $this->insert($settings);
		}

		if ($batchSetting === Settings::EMAIL_SEND_WEEKLY) {
			$batchTime = 3600 * 24 * 7;
		} elseif ($batchSetting === Settings::EMAIL_SEND_DAILY) {
			$batchTime = 3600 * 24;
		} elseif ($batchSetting === Settings::EMAIL_SEND_3HOURLY) {
			$batchTime = 3600 * 3;
		} elseif ($batchSetting === Settings::EMAIL_SEND_HOURLY) {
			$batchTime = 3600;
		} else {
			$batchTime = 0; // Off
		}

		$settings->setBatchTime($batchTime);
		if ($batchTime === 0) {
			// When mails are Off, we don't set a "next send time" so it can be
			// skipped in the background job.
			$settings->setNextSendTime(0);
		} else {
			// This will automatically heal on the first run of the background job.
			// We are just setting it to 1, so it's checked soon in case
			// the time is now shorter and should trigger already.
			$settings->setNextSendTime(1);
		}
		$this->update($settings);
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
			'user_id' => (string)$row['user_id'],
			'batch_time' => (int)$row['batch_time'],
			'last_send_id' => (int)$row['last_send_id'],
			'next_send_time' => (int)$row['next_send_time'],
		]);
	}
}
