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
	/** @var IDBConnection */
	protected $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
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
		}
		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
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
		$result = $query->execute();

		while ($row = $result->fetch()) {
			$insert
				->setParameter('uid', $row['uid'])
				->setParameter('token', (int) $row['token'], IQueryBuilder::PARAM_INT)
				->setParameter('deviceidentifier', $row['deviceidentifier'])
				->setParameter('devicepublickey', $row['devicepublickey'])
				->setParameter('devicepublickeyhash', $row['devicepublickeyhash'])
				->setParameter('pushtokenhash', $row['pushtokenhash'])
				->setParameter('proxyserver', $row['proxyserver'])
				->setParameter('apptype', $row['apptype'])
			;

			$insert->execute();
		}
		$result->closeCursor();
	}
}
