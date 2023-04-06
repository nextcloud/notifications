<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2023, Joas Schilling <coding@schilljs.com>
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

namespace OCA\Notifications\Service;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IRequest;

class ClientService {
	public const DESKTOP_CLIENT_TIMEOUT = 120;

	public function __construct(
		protected IDBConnection $connection,
		protected IRequest $request,
	) {
	}

	public function hasTalkDesktop(string $userId, int $maxAge = 0): bool {
		$query = $this->connection->getQueryBuilder();
		$query->select('name')
			->from('authtoken')
			->where($query->expr()->eq('uid', $query->createNamedParameter($userId)));

		if ($maxAge !== 0) {
			$query->andWhere($query->expr()->gte(
				'last_activity',
				$query->createNamedParameter($maxAge, IQueryBuilder::PARAM_INT)
			));
		}

		$result = $query->executeQuery();
		while ($row = $result->fetch()) {
			if (preg_match('/ \(Talk Desktop Client - [A-Za-z ]+\)$/', $row['name'])) {
				$result->closeCursor();
				return true;
			}
		}
		$result->closeCursor();

		return false;
	}
}
