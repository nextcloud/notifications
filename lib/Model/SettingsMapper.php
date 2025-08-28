<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Model;

use OCP\AppFramework\Db\DoesNotExistException;
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
	 */
	public function getSettingsByUser(string $userId): Settings {
		try {
			$query = $this->db->getQueryBuilder();

			$query->select('*')
				->from($this->getTableName())
				->where($query->expr()->eq('user_id', $query->createNamedParameter($userId)));

			return $this->findEntity($query);
		} catch (DoesNotExistException) {
			$settings = new Settings();
			$settings->setUserId($userId);
			$settings->setBatchTime(Settings::EMAIL_SEND_DEFAULT);
			/** @var Settings $settings */
			$settings = $this->insert($settings);

			return $settings;
		}
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

	public function setBatchSettingForUser(Settings $settings, int $batchSetting): Settings {
		$batchTime = self::batchSettingToTime($batchSetting);
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
		return $settings;
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

	public static function batchSettingToTime(int $batchSetting): int {
		return match ($batchSetting) {
			Settings::EMAIL_SEND_WEEKLY => 3600 * 24 * 7,
			Settings::EMAIL_SEND_DAILY => 3600 * 24,
			Settings::EMAIL_SEND_3HOURLY => 3600 * 3,
			Settings::EMAIL_SEND_HOURLY => 3600,
			Settings::EMAIL_SEND_DEFAULT => Settings::EMAIL_SEND_DEFAULT,
			default => 0,
		};
	}

	public static function batchTimeToSetting(int $batchTime): int {
		return match ($batchTime) {
			3600 * 24 * 7 => Settings::EMAIL_SEND_WEEKLY,
			3600 * 24 => Settings::EMAIL_SEND_DAILY,
			3600 * 3 => Settings::EMAIL_SEND_3HOURLY,
			3600 => Settings::EMAIL_SEND_HOURLY,
			Settings::EMAIL_SEND_DEFAULT => Settings::EMAIL_SEND_DEFAULT,
			default => Settings::EMAIL_SEND_OFF,
		};
	}
}
