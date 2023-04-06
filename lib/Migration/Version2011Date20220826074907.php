<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022, Joas Schilling <coding@schilljs.com>
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

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version2011Date20220826074907 extends SimpleMigrationStep {
	/** @var IDBConnection */
	protected $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

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
