<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Julien Barnoin <julien@barnoin.com>
 *
 * @license GNU AGPL version 3 or any later version
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

namespace OCA\Notifications\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;
use OCP\IUserSession;
use OCP\IInitialStateService;
use OCP\Util;

class Personal implements ISettings {
	/** @var \OCP\IConfig */
	protected $config;

	/** @var \OCP\IL10N */
	protected $l10n;

	/** @var IUserSession */
	private $session;

	/** @var IInitialStateService */
	private $initialStateService;

	public const EMAIL_SEND_HOURLY = 0;
	public const EMAIL_SEND_DAILY = 1;
	public const EMAIL_SEND_WEEKLY = 2;
	public const EMAIL_SEND_ASAP = 3;

	public function __construct(IConfig $config,
								IL10N $l10n,
								IUserSession $session,
								IInitialStateService $initialStateService) {
		$this->config = $config;
		$this->l10n = $l10n;

		$this->session = $session;
		$this->initialStateService = $initialStateService;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		Util::addScript('notifications', 'notifications-userSettings');
		
		$settingBatchTime = Personal::EMAIL_SEND_HOURLY;
		$user = $this->session->getUser()->getUID();
		$currentSetting = (int) $this->config->getUserValue($user, 'notifications', 'notify_setting_batchtime', 3600 * 24);

		if ($currentSetting === 3600 * 24 * 7) {
			$settingBatchTime = Personal::EMAIL_SEND_WEEKLY;
		} elseif ($currentSetting === 3600 * 24) {
			$settingBatchTime = Personal::EMAIL_SEND_DAILY;
		} elseif ($currentSetting === 0) {
			$settingBatchTime = Personal::EMAIL_SEND_ASAP;
		}

		$emailEnabled = true;
		
		$this->initialStateService->provideInitialState('notifications', 'config', [
			'setting' => 'personal',
			'is_email_set' => !empty($this->config->getUserValue($user, 'settings', 'email', '')),
			'email_enabled' => $emailEnabled,
			'setting_batchtime' => $settingBatchTime,
			'notifications_email_enabled' => $this->config->getUserValue($user, 'notifications', 'notifications_email_enabled') == 1
		]);

		return new TemplateResponse('notifications', 'settings/personal');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection(): string {
		return 'notifications';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority(): int {
		return 55;
	}
}
