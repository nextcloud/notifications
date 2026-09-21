<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Add snoozed_until column to support server-side notification snoozing
 */
class Version9000Date20260921120000 extends SimpleMigrationStep {

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

		$table = $schema->getTable('notifications');
		$changed = false;

		if (!$table->hasColumn('snoozed_until')) {
			$table->addColumn('snoozed_until', Types::INTEGER, [
				'notnull' => true,
				'length' => 4,
				'default' => 0,
			]);
			$changed = true;
		}

		if (!$table->hasIndex('oc_notif_snoozed_until')) {
			$table->addIndex(['snoozed_until'], 'oc_notif_snoozed_until');
			$changed = true;
		}

		return $changed ? $schema : null;
	}
}
