<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
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

	/**
	 * @param IDBConnection $connection
	 * @param IManager $manager
	 */
	public function __construct(IDBConnection $connection, IManager $manager) {
		$this->connection = $connection;
		$this->manager = $manager;
	}

	/**
	 * Add a new notification to the database
	 *
	 * @param INotification $notification
	 * @return int
	 */
	public function add(INotification $notification) {
		$sql = $this->connection->getQueryBuilder();
		$sql->insert('notifications');
		$this->sqlInsert($sql, $notification);
		$sql->execute();
	}

	/**
	 * Count the notifications matching the given Notification
	 *
	 * @param INotification $notification
	 * @return int
	 */
	public function count(INotification $notification) {
		$sql = $this->connection->getQueryBuilder();
		$sql->select($sql->createFunction('COUNT(*)'))
			->from('notifications');

		$this->sqlWhere($sql, $notification);

		$statement = $sql->execute();
		$count = (int) $statement->fetchColumn();
		$statement->closeCursor();

		return $count;
	}

	/**
	 * Delete the notifications matching the given Notification
	 *
	 * @param INotification $notification
	 * @return null
	 */
	public function delete(INotification $notification) {
		$sql = $this->connection->getQueryBuilder();
		$sql->delete('notifications');
		$this->sqlWhere($sql, $notification);
		$sql->execute();
	}

	/**
	 * Delete the notification matching the given id
	 *
	 * @param int $id
	 * @param string $user
	 * @return null
	 */
	public function deleteById($id, $user) {
		$sql = $this->connection->getQueryBuilder();
		$sql->delete('notifications')
			->where($sql->expr()->eq('notification_id', $sql->createParameter('id')))
			->setParameter('id', $id)
			->andWhere($sql->expr()->eq('user', $sql->createParameter('user')))
			->setParameter('user', $user);
		$sql->execute();
	}

	/**
	 * Get the notification matching the given id
	 *
	 * @param int $id
	 * @param string $user
	 * @return null|INotification
	 */
	public function getById($id, $user) {
		$sql = $this->connection->getQueryBuilder();
		$sql->select('*')
			->from('notifications')
			->where($sql->expr()->eq('notification_id', $sql->createParameter('id')))
			->setParameter('id', $id)
			->andWhere($sql->expr()->eq('user', $sql->createParameter('user')))
			->setParameter('user', $user);
		$statement = $sql->execute();

		$notification = null;
		if ($row = $statement->fetch()) {
			$notification = $this->notificationFromRow($row);
		}
		$statement->closeCursor();

		return $notification;
	}

	/**
	 * Return the notifications matching the given Notification
	 *
	 * @param INotification $notification
	 * @param int $limit
	 * @return array [notification_id => INotification]
	 */
	public function get(INotification $notification, $limit = 25) {
		$sql = $this->connection->getQueryBuilder();
		$sql->select('*')
			->from('notifications')
			->orderBy('notification_id', 'DESC')
			->setMaxResults($limit);

		$this->sqlWhere($sql, $notification);
		$statement = $sql->execute();

		$notifications = [];
		while ($row = $statement->fetch()) {
			$notifications[(int) $row['notification_id']] = $this->notificationFromRow($row);
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
			$sql->andWhere($sql->expr()->eq('app', $sql->createParameter('app')));
			$sql->setParameter('app', $notification->getApp());
		}

		if ($notification->getUser() !== '') {
			$sql->andWhere($sql->expr()->eq('user', $sql->createParameter('user')))
				->setParameter('user', $notification->getUser());
		}

		if ($notification->getDateTime()->getTimestamp() !== 0) {
			$sql->andWhere($sql->expr()->eq('timestamp', $sql->createParameter('timestamp')))
				->setParameter('timestamp', $notification->getDateTime()->getTimestamp());
		}

		if ($notification->getObjectType() !== '') {
			$sql->andWhere($sql->expr()->eq('object_type', $sql->createParameter('objectType')))
				->setParameter('objectType', $notification->getObjectType());
		}

		if ($notification->getObjectId() !== '') {
			$sql->andWhere($sql->expr()->eq('object_id', $sql->createParameter('objectId')))
				->setParameter('objectId', $notification->getObjectId());
		}

		if ($notification->getSubject() !== '') {
			$sql->andWhere($sql->expr()->eq('subject', $sql->createParameter('subject')))
				->setParameter('subject', $notification->getSubject());
		}

		if ($notification->getMessage() !== '') {
			$sql->andWhere($sql->expr()->eq('message', $sql->createParameter('message')))
				->setParameter('message', $notification->getMessage());
		}

		if ($notification->getLink() !== '') {
			$sql->andWhere($sql->expr()->eq('link', $sql->createParameter('link')))
				->setParameter('link', $notification->getLink());
		}
	}

	/**
	 * Turn a notification into an input statement
	 *
	 * @param IQueryBuilder $sql
	 * @param INotification $notification
	 */
	protected function sqlInsert(IQueryBuilder $sql, INotification $notification) {
		$sql->setValue('app', $sql->createParameter('app'))
			->setParameter('app', $notification->getApp());

		$sql->setValue('user', $sql->createParameter('user'))
			->setParameter('user', $notification->getUser());

		$sql->setValue('timestamp', $sql->createParameter('timestamp'))
			->setParameter('timestamp', $notification->getDateTime()->getTimestamp());

		$sql->setValue('object_type', $sql->createParameter('objectType'))
			->setParameter('objectType', $notification->getObjectType());

		$sql->setValue('object_id', $sql->createParameter('objectId'))
			->setParameter('objectId', $notification->getObjectId());

		$sql->setValue('subject', $sql->createParameter('subject'))
			->setParameter('subject', $notification->getSubject());

		$sql->setValue('subject_parameters', $sql->createParameter('subject_parameters'))
			->setParameter('subject_parameters', json_encode($notification->getSubjectParameters()));

		$sql->setValue('message', $sql->createParameter('message'))
			->setParameter('message', $notification->getMessage());

		$sql->setValue('message_parameters', $sql->createParameter('message_parameters'))
			->setParameter('message_parameters', json_encode($notification->getMessageParameters()));

		$sql->setValue('link', $sql->createParameter('link'))
			->setParameter('link', $notification->getLink());

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
		$sql->setValue('actions', $sql->createParameter('actions'))
			->setParameter('actions', json_encode($actions));
	}

	/**
	 * Turn a database row into a INotification
	 *
	 * @param array $row
	 * @return INotification
	 */
	protected function notificationFromRow(array $row) {
		$dateTime = new \DateTime();
		$dateTime->setTimestamp((int) $row['timestamp']);

		$notification = $this->manager->createNotification();
		$notification->setApp($row['app'])
			->setUser($row['user'])
			->setDateTime($dateTime)
			->setObject($row['object_type'], $row['object_id'])
			->setSubject($row['subject'], (array) json_decode($row['subject_parameters'], true));

		if ($row['message'] !== '') {
			$notification->setMessage($row['message'], (array) json_decode($row['message_parameters'], true));
		}
		if ($row['link'] !== '') {
			$notification->setLink($row['link']);
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
