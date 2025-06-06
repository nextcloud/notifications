<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version2011Date20210930134607 extends SimpleMigrationStep {
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

		if (!$schema->hasTable('notifications_settings')) {
			$table = $schema->createTable('notifications_settings');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('batch_time', Types::INTEGER, [
				'default' => 0,
				'length' => 4,
			]);
			$table->addColumn('last_send_id', Types::BIGINT, [
				'default' => 0,
			]);
			$table->addColumn('next_send_time', Types::INTEGER, [
				'default' => 0,
				'length' => 11,
			]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['user_id'], 'notset_user');
			$table->addIndex(['next_send_time'], 'notset_nextsend');

			return $schema;
		}

		return null;
	}
}
