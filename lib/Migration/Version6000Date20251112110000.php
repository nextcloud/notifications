<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */


namespace OCA\Notifications\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Create database table to webpush support
 */
class Version6000Date20251112110000 extends SimpleMigrationStep {

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

		if (!$schema->hasTable('notifications_webpush')) {
			$table = $schema->createTable('notifications_webpush');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			// uid+token identifies a device
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('token', Types::BIGINT, [
				'default' => 0,
			]);
			$table->addColumn('endpoint', Types::STRING, [
				'notnull' => true,
				'length' => 767,
			]);
			$table->addColumn('p256dh', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('auth', Types::STRING, [
				'notnull' => true,
				'length' => 32,
			]);
			$table->addColumn('app_types', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('activated', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false
			]);
			$table->addColumn('activation_token', Types::STRING, [
				'notnull' => true,
				'length' => 36
			]);

			$table->setPrimaryKey(['id']);
			// Allow a single push subscription per device
			$table->addUniqueIndex(['uid', 'token'], 'notifwebpush_uid_token');
			// If the push endpoint is removed, we will delete the row based on the endpoint
			$table->addIndex(['endpoint'], 'notifwebpush_endpoint');
			return $schema;
		}
		return null;
	}
}
