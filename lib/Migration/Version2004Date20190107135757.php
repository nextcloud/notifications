<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version2004Date20190107135757 extends SimpleMigrationStep {
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

		if (!$schema->hasTable('notifications')) {
			$table = $schema->createTable('notifications');
			$table->addColumn('notification_id', Types::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('app', Types::STRING, [
				'notnull' => true,
				'length' => 32,
			]);
			$table->addColumn('user', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('timestamp', Types::INTEGER, [
				'notnull' => true,
				'length' => 4,
				'default' => 0,
			]);
			$table->addColumn('object_type', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('object_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('subject', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('subject_parameters', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('message', Types::STRING, [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('message_parameters', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('link', Types::STRING, [
				'notnull' => false,
				'length' => 4000,
			]);
			$table->addColumn('icon', Types::STRING, [
				'notnull' => false,
				'length' => 4000,
			]);
			$table->addColumn('actions', Types::TEXT, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['notification_id']);
			$table->addIndex(['app'], 'oc_notifications_app');
			$table->addIndex(['user'], 'oc_notifications_user');
			$table->addIndex(['timestamp'], 'oc_notifications_timestamp');
			$table->addIndex(['object_type', 'object_id'], 'oc_notifications_object');
		}

		// $schema->createTable('notifications_pushtokens') was
		// replaced with notifications_pushhash in Version2010Date20210218082811
		// and deleted in Version2010Date20210218082855
		return $schema;
	}
}
