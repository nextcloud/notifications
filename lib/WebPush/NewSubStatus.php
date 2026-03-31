<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\WebPush;

enum NewSubStatus: int {
	case CREATED = 0;
	case UPDATED = 1;
	case ERROR = 2;
}
