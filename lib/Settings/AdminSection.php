<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
	public function __construct(
		private IURLGenerator $url,
		private IL10N $l,
	) {
	}

	#[\Override]
	public function getIcon(): string {
		return $this->url->imagePath('notifications', 'notifications-dark.svg');
	}

	#[\Override]
	public function getID(): string {
		return 'notifications';
	}

	#[\Override]
	public function getName(): string {
		return $this->l->t('Notifications');
	}

	#[\Override]
	public function getPriority(): int {
		return 55;
	}
}
