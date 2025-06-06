<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */


namespace OCA\Notifications\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\DB\Types;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Recreate notifications_pushtoken(s) with a primary key for cluster support
 */
class Version2010Date20210218082811 extends SimpleMigrationStep {
	public function __construct(
		protected IDBConnection $connection,
	) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	#[\Override]
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('notifications_pushhash')) {
			$table = $schema->createTable('notifications_pushhash');
			$table->addColumn('id', Types::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('token', Types::INTEGER, [
				'notnull' => true,
				'length' => 4,
				'default' => 0,
			]);
			$table->addColumn('deviceidentifier', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('devicepublickey', Types::STRING, [
				'notnull' => true,
				'length' => 512,
			]);
			$table->addColumn('devicepublickeyhash', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('pushtokenhash', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('proxyserver', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('apptype', Types::STRING, [
				'notnull' => true,
				'length' => 32,
				'default' => 'unknown',
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['uid', 'token'], 'oc_npushhash_uid');
			$table->addIndex(['deviceidentifier'], 'oc_npushhash_di');
		}
		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	#[\Override]
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		if (!$this->connection->tableExists('notifications_pushtokens')) {
			return;
		}

		$insert = $this->connection->getQueryBuilder();
		$insert->insert('notifications_pushhash')
			->values([
				'uid' => $insert->createParameter('uid'),
				'token' => $insert->createParameter('token'),
				'deviceidentifier' => $insert->createParameter('deviceidentifier'),
				'devicepublickey' => $insert->createParameter('devicepublickey'),
				'devicepublickeyhash' => $insert->createParameter('devicepublickeyhash'),
				'pushtokenhash' => $insert->createParameter('pushtokenhash'),
				'proxyserver' => $insert->createParameter('proxyserver'),
				'apptype' => $insert->createParameter('apptype'),
			]);

		$query = $this->connection->getQueryBuilder();
		$query->select('*')
			->from('notifications_pushtokens');
		$result = $query->executeQuery();

		while ($row = $result->fetch()) {
			$insert
				->setParameter('uid', $row['uid'])
				->setParameter('token', (int)$row['token'], IQueryBuilder::PARAM_INT)
				->setParameter('deviceidentifier', $row['deviceidentifier'])
				->setParameter('devicepublickey', $row['devicepublickey'])
				->setParameter('devicepublickeyhash', $row['devicepublickeyhash'])
				->setParameter('pushtokenhash', $row['pushtokenhash'])
				->setParameter('proxyserver', $row['proxyserver'])
				->setParameter('apptype', $row['apptype'])
			;

			$insert->executeStatement();
		}
		$result->closeCursor();
	}
}
