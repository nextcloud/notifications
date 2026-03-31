<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\WebPush;

enum ActivationSubStatus: int {
	case CREATED = 0;
	case OK = 1;
	case NO_TOKEN = 2;
	case NO_SUB = 3;
}
