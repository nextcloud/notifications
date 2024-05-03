<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
