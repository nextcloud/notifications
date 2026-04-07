<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\BackgroundJob;

use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IConfig;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;

class CleanupOutdatedNotifications extends TimedJob {
	public function __construct(
		ITimeFactory $time,
		private IConfig $config,
		private LoggerInterface $logger,
		private IDBConnection $db,
	) {
		parent::__construct($time);
		$this->setTimeSensitivity(self::TIME_INSENSITIVE);
		$this->setInterval(24 * 60 * 60);
	}

	#[\Override]
	protected function run($argument): void {
		// Remove notifications that are older than one year
		$expireDays = $this->config->getSystemValue('notifications_expire_days', 365);
		$count = $this->cleanupOldNotifications($expireDays);
		$this->logger->info('Cleaned up ' . $count . ' notifications that are older than ' . $expireDays . ' days',
			['count' => $count]);

		// Remove notifications from users that have more than the limit
		$excessLimit = $this->config->getSystemValue('notifications_excess_limit', 10_000);
		$count = $this->cleanupExcessNotifications($excessLimit);
		$this->logger->info('Cleaned up ' . $count . ' notifications from users that excess ' . $excessLimit . ' notifications',
			['count' => $count]);
	}

	private function cleanupOldNotifications(int $expireDays): int {
		$deleteFrom = $this->time->getDateTime('-' . $expireDays . ' days');
		$qb = $this->db->getQueryBuilder();

		return $qb->delete('notifications')
			->where(
				$qb->expr()->lt('timestamp', $qb->createNamedParameter($deleteFrom->getTimestamp())),
			)
			->executeStatement();
	}

	private function cleanupExcessNotifications(int $limit): int {
		$usersWithExcessNotifications = $this->getUsersWithExcessNotifications($limit);

		$deleted = 0;
		foreach ($usersWithExcessNotifications as $user) {
			$thresholdId = $this->getThresholdNotificationId($user, $limit);

			if ($thresholdId === null) {
				continue;
			}

			$qb = $this->db->getQueryBuilder();
			$deleted += $qb->delete('notifications')
				->where($qb->expr()->eq('user', $qb->createNamedParameter($user)))
				->andWhere(
					$qb->expr()->lt('notification_id', $qb->createNamedParameter($thresholdId)),
				)
				->executeStatement();
		}

		return $deleted;
	}

	/**
	 * @return string[]
	 */
	private function getUsersWithExcessNotifications(int $limit): array {
		$qb = $this->db->getQueryBuilder();

		return $qb
			->select('user')
			->from('notifications')
			->groupBy('user')
			->having(
				$qb->expr()->gt(
					$qb->func()->count(),
					$qb->createNamedParameter($limit),
				),
			)
			->setMaxResults(1000)
			->executeQuery()
			->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * Returns the notification_id at position LIMIT_BY_COUNT_PER_USER (ordered DESC)
	 * All notifications with ID less than this threshold will be deleted
	 */
	private function getThresholdNotificationId(string $user, int $limit): ?int {
		$qb = $this->db->getQueryBuilder();

		$result = $qb
			->select('notification_id')
			->from('notifications')
			->where($qb->expr()->eq('user', $qb->createNamedParameter($user)))
			->orderBy('notification_id', 'DESC')
			->setFirstResult($limit)
			->setMaxResults(1)
			->executeQuery()
			->fetchOne();

		return $result !== false ? (int)$result : null;
	}
}
