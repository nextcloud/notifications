<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class PersonalSection implements IIconSection {
	public function __construct(
		protected IURLGenerator $url,
		protected IL10N $l,
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
		return 10;
	}
}
