<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023, Joas Schilling <coding@schilljs.com>
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
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

namespace OCA\Notifications;

use OCA\Notifications\Exceptions\NotificationNotFoundException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;

class Handler {
	/** @var IDBConnection */
	protected $connection;

	/** @var IManager */
	protected $manager;

	public function __construct(IDBConnection $connection,
		IManager $manager) {
		$this->connection = $connection;
		$this->manager = $manager;
	}

	/**
	 * Add a new notification to the database
	 *
	 * @param INotification $notification
	 * @return int
	 */
	public function add(INotification $notification): int {
		$sql = $this->connection->getQueryBuilder();
		$sql->insert('notifications');
		$this->sqlInsert($sql, $notification);
		$sql->executeStatement();

		return $sql->getLastInsertId();
	}

	/**
	 * Count the notifications matching the given Notification
	 *
	 * @param INotification $notification
	 * @return int
	 */
	public function count(INotification $notification): int {
		$sql = $this->connection->getQueryBuilder();
		$sql->select($sql->createFunction('COUNT(*)'))
			->from('notifications');

		$this->sqlWhere($sql, $notification);

		$statement = $sql->executeQuery();
		$count = (int) $statement->fetchOne();
		$statement->closeCursor();

		return $count;
	}

	/**
	 * Delete the notifications matching the given Notification
	 *
	 * @param INotification $notification
	 * @return array A Map with all deleted notifications [user => [notifications]]
	 */
	public function delete(INotification $notification): array {
		$sql = $this->connection->getQueryBuilder();
		$sql->select('*')
			->from('notifications');

		$this->sqlWhere($sql, $notification);
		$statement = $sql->executeQuery();

		$deleted = [];
		$notifications = [];
		while ($row = $statement->fetch()) {
			if (!isset($deleted[$row['user']])) {
				$deleted[$row['user']] = [];
			}

			$deleted[$row['user']][] = [
				'id' => (int) $row['notification_id'],
				'app' => $row['app'],
			];
			$notifications[(int) $row['notification_id']] = $this->notificationFromRow($row);
		}
		$statement->closeCursor();

		if (count($notifications) === 0) {
			return [];
		}

		$this->connection->beginTransaction();
		try {
			$shouldFlush = $this->manager->defer();

			foreach ($notifications as $n) {
				$this->manager->dismissNotification($n);
			}

			$notificationIds = array_keys($notifications);
			foreach (array_chunk($notificationIds, 1000) as $chunk) {
				$this->deleteIds($chunk);
			}

			if ($shouldFlush) {
				$this->manager->flush();
			}
		} catch (\Throwable $e) {
			$this->connection->rollBack();
			throw $e;
		}
		$this->connection->commit();

		return $deleted;
	}

	/**
	 * Delete the notification of a given user
	 *
	 * @param string $user
	 * @return bool
	 */
	public function deleteByUser(string $user): bool {
		$notification = $this->manager->createNotification();
		try {
			$notification->setUser($user);
		} catch (\InvalidArgumentException $e) {
			return false;
		}
		return !empty($this->delete($notification));
	}

	/**
	 * Delete the notification matching the given id
	 *
	 * @param int $id
	 * @param string $user
	 * @param INotification|null $notification
	 * @return bool
	 * @throws NotificationNotFoundException
	 */
	public function deleteById(int $id, string $user, ?INotification $notification = null): bool {
		if (!$notification instanceof INotification) {
			$notification = $this->getById($id, $user);
		}

		$this->manager->dismissNotification($notification);

		$sql = $this->connection->getQueryBuilder();
		$sql->delete('notifications')
			->where($sql->expr()->eq('notification_id', $sql->createNamedParameter($id)))
			->andWhere($sql->expr()->eq('user', $sql->createNamedParameter($user)));
		return (bool) $sql->executeStatement();
	}

	/**
	 * Delete the notification matching the given ids
	 *
	 * @param int[] $ids
	 */
	public function deleteIds(array $ids): void {
		$sql = $this->connection->getQueryBuilder();
		$sql->delete('notifications')
			->where($sql->expr()->in('notification_id', $sql->createNamedParameter($ids, IQueryBuilder::PARAM_INT_ARRAY)));
		$sql->executeStatement();
	}

	/**
	 * Get the notification matching the given id
	 *
	 * @param int $id
	 * @param string $user
	 * @return INotification
	 * @throws NotificationNotFoundException
	 */
	public function getById(int $id, string $user): INotification {
		$sql = $this->connection->getQueryBuilder();
		$sql->select('*')
			->from('notifications')
			->where($sql->expr()->eq('notification_id', $sql->createNamedParameter($id)))
			->andWhere($sql->expr()->eq('user', $sql->createNamedParameter($user)));
		$statement = $sql->executeQuery();
		$row = $statement->fetch();
		$statement->closeCursor();

		if ($row === false) {
			throw new NotificationNotFoundException('No entry returned from database');
		}

		try {
			return $this->notificationFromRow($row);
		} catch (\InvalidArgumentException $e) {
			throw new NotificationNotFoundException('Could not create notification from database row');
		}
	}

	/**
	 * Confirm that the notification ids still exist for the user
	 *
	 * @param string $user
	 * @param int[] $ids
	 * @return int[]
	 */
	public function confirmIdsForUser(string $user, array $ids): array {
		$query = $this->connection->getQueryBuilder();
		$query->select('notification_id')
			->from('notifications')
			->where($query->expr()->in('notification_id', $query->createNamedParameter($ids, IQueryBuilder::PARAM_INT_ARRAY)))
			->andWhere($query->expr()->eq('user', $query->createNamedParameter($user)));
		$result = $query->executeQuery();

		$existing = [];
		while ($row = $result->fetch()) {
			$existing[] = (int) $row['notification_id'];
		}
		$result->closeCursor();

		return $existing;
	}

	/**
	 * Get the notifications after (and excluding) the given id
	 *
	 * @param int $startAfterId
	 * @param string $userId
	 * @param int $limit
	 * @return array [notification_id => INotification]
	 */
	public function getAfterId(int $startAfterId, string $userId, int $limit = 25): array {
		$sql = $this->connection->getQueryBuilder();
		$sql->select('*')
			->from('notifications')
			->where($sql->expr()->gt('notification_id', $sql->createNamedParameter($startAfterId)))
			->andWhere($sql->expr()->eq('user', $sql->createNamedParameter($userId)))
			->orderBy('notification_id', 'DESC')
			->setMaxResults($limit);
		$statement = $sql->executeQuery();

		$notifications = [];
		while ($row = $statement->fetch()) {
			try {
				$notifications[(int)$row['notification_id']] = $this->notificationFromRow($row);
			} catch (\InvalidArgumentException $e) {
				continue;
			}
		}
		$statement->closeCursor();

		return $notifications;
	}

	/**
	 * Return the notifications matching the given Notification
	 *
	 * @param INotification $notification
	 * @param int $limit
	 * @return array [notification_id => INotification]
	 */
	public function get(INotification $notification, $limit = 25): array {
		$sql = $this->connection->getQueryBuilder();
		$sql->select('*')
			->from('notifications')
			->orderBy('notification_id', 'DESC')
			->setMaxResults($limit);

		$this->sqlWhere($sql, $notification);
		$statement = $sql->executeQuery();

		$notifications = [];
		while ($row = $statement->fetch()) {
			try {
				$notifications[(int)$row['notification_id']] = $this->notificationFromRow($row);
			} catch (\InvalidArgumentException $e) {
				continue;
			}
		}
		$statement->closeCursor();

		return $notifications;
	}

	/**
	 * Add where statements to a query builder matching the given notification
	 *
	 * @param IQueryBuilder $sql
	 * @param INotification $notification
	 */
	protected function sqlWhere(IQueryBuilder $sql, INotification $notification) {
		if ($notification->getApp() !== '') {
			$sql->andWhere($sql->expr()->eq('app', $sql->createNamedParameter($notification->getApp())));
		}

		if ($notification->getUser() !== '') {
			$sql->andWhere($sql->expr()->eq('user', $sql->createNamedParameter($notification->getUser())));
		}

		$timestamp = $notification->getDateTime()->getTimestamp();
		if ($timestamp !== 0) {
			$sql->andWhere($sql->expr()->eq('timestamp', $sql->createNamedParameter($timestamp)));
		}

		if ($notification->getObjectType() !== '') {
			$sql->andWhere($sql->expr()->eq('object_type', $sql->createNamedParameter($notification->getObjectType())));
		}

		if ($notification->getObjectId() !== '') {
			$sql->andWhere($sql->expr()->eq('object_id', $sql->createNamedParameter($notification->getObjectId())));
		}

		if ($notification->getSubject() !== '') {
			$sql->andWhere($sql->expr()->eq('subject', $sql->createNamedParameter($notification->getSubject())));
		}

		if ($notification->getMessage() !== '') {
			$sql->andWhere($sql->expr()->eq('message', $sql->createNamedParameter($notification->getMessage())));
		}
	}

	/**
	 * Turn a notification into an input statement
	 *
	 * @param IQueryBuilder $sql
	 * @param INotification $notification
	 */
	protected function sqlInsert(IQueryBuilder $sql, INotification $notification) {
		$actions = [];
		foreach ($notification->getActions() as $action) {
			/** @var IAction $action */
			$actions[] = [
				'label' => $action->getLabel(),
				'link' => $action->getLink(),
				'type' => $action->getRequestType(),
				'primary' => $action->isPrimary(),
			];
		}

		$sql->setValue('app', $sql->createNamedParameter($notification->getApp()))
			->setValue('user', $sql->createNamedParameter($notification->getUser()))
			->setValue('timestamp', $sql->createNamedParameter($notification->getDateTime()->getTimestamp()))
			->setValue('object_type', $sql->createNamedParameter($notification->getObjectType()))
			->setValue('object_id', $sql->createNamedParameter($notification->getObjectId()))
			->setValue('subject', $sql->createNamedParameter($notification->getSubject()))
			->setValue('subject_parameters', $sql->createNamedParameter(json_encode($notification->getSubjectParameters())))
			->setValue('message', $sql->createNamedParameter($notification->getMessage()))
			->setValue('message_parameters', $sql->createNamedParameter(json_encode($notification->getMessageParameters())))
			->setValue('link', $sql->createNamedParameter($notification->getLink()))
			->setValue('icon', $sql->createNamedParameter($notification->getIcon()))
			->setValue('actions', $sql->createNamedParameter(json_encode($actions)));
	}

	/**
	 * Turn a database row into a INotification
	 *
	 * @param array $row
	 * @return INotification
	 * @throws \InvalidArgumentException
	 */
	protected function notificationFromRow(array $row): INotification {
		$dateTime = new \DateTime();
		$dateTime->setTimestamp((int) $row['timestamp']);

		$notification = $this->manager->createNotification();
		$notification->setApp($row['app'])
			->setUser($row['user'])
			->setDateTime($dateTime)
			->setObject($row['object_type'], $row['object_id'])
			->setSubject($row['subject'], (array) json_decode($row['subject_parameters'], true));

		if ($row['message'] !== '' && $row['message'] !== null) {
			$notification->setMessage($row['message'], (array) json_decode($row['message_parameters'], true));
		}
		if ($row['link'] !== '' && $row['link'] !== null) {
			$notification->setLink($row['link']);
		}
		if ($row['icon'] !== '' && $row['icon'] !== null) {
			$notification->setIcon($row['icon']);
		}

		$actions = (array) json_decode($row['actions'], true);
		foreach ($actions as $actionData) {
			$action = $notification->createAction();
			$action->setLabel($actionData['label'])
				->setLink($actionData['link'], $actionData['type']);
			if (isset($actionData['primary'])) {
				$action->setPrimary($actionData['primary']);
			}
			$notification->addAction($action);
		}

		return $notification;
	}
}
