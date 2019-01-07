<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019 Joas Schilling <coding@schilljs.com>
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

namespace OCA\Notifications\Migration;

use Closure;
use Doctrine\DBAL\Types\Type;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version2004Date20190107135757 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('notifications')) {
			$table = $schema->createTable('notifications');
			$table->addColumn('notification_id', Type::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('app', Type::STRING, [
				'notnull' => true,
				'length' => 32,
			]);
			$table->addColumn('user', Type::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('timestamp', Type::INTEGER, [
				'notnull' => true,
				'length' => 4,
				'default' => 0,
			]);
			$table->addColumn('object_type', Type::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('object_id', Type::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('subject', Type::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('subject_parameters', Type::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('message', Type::STRING, [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('message_parameters', Type::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('link', Type::STRING, [
				'notnull' => false,
				'length' => 4000,
			]);
			$table->addColumn('icon', Type::STRING, [
				'notnull' => false,
				'length' => 4000,
			]);
			$table->addColumn('actions', Type::TEXT, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['notification_id']);
			$table->addIndex(['app'], 'oc_notifications_app');
			$table->addIndex(['user'], 'oc_notifications_user');
			$table->addIndex(['timestamp'], 'oc_notifications_timestamp');
			$table->addIndex(['object_type', 'object_id'], 'oc_notifications_object');
		}

		if (!$schema->hasTable('notifications_pushtokens')) {
			$table = $schema->createTable('notifications_pushtokens');
			$table->addColumn('uid', Type::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('token', Type::INTEGER, [
				'notnull' => true,
				'length' => 4,
				'default' => 0,
			]);
			$table->addColumn('deviceidentifier', Type::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('devicepublickey', Type::STRING, [
				'notnull' => true,
				'length' => 512,
			]);
			$table->addColumn('devicepublickeyhash', Type::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('pushtokenhash', Type::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('proxyserver', Type::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('apptype', Type::STRING, [
				'notnull' => true,
				'length' => 32,
				'default' => 'unknown',
			]);
			$table->addUniqueIndex(['uid', 'token'], 'oc_notifpushtoken');
		}
		return $schema;
	}

}
