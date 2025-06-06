<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Migration;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version2011Date20220826074907 extends SimpleMigrationStep {
	public function __construct(
		protected IDBConnection $connection,
	) {
	}

	#[\Override]
	public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options) {
		$query = $this->connection->getQueryBuilder();

		// The maximum valid value is NOW + 7 days, but since updating is fixed
		// and you only run into the bug at the year 2038, we can also count up 8 days.
		$time = time() + 3600 * 24 * 8;

		$query->update('notifications_settings')
			->set('next_send_time', $query->createNamedParameter(1, IQueryBuilder::PARAM_INT))
			->where($query->expr()->gt('next_send_time', $query->createNamedParameter($time, IQueryBuilder::PARAM_INT)));
		$count = $query->executeStatement();

		if ($count > 0) {
			$output->info('Fixed next send of ' . $count . ' disabled users');
		}
	}
}
