<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Controller;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		protected IConfig $config,
		protected SettingsMapper $settingsMapper,
		protected string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Update personal notification settings
	 *
	 * @param int $batchSetting How often E-mails about missed notifications should be sent (hourly: 1; every three hours: 2; daily: 3; weekly: 4)
	 * @param string $soundNotification Enable sound for notifications ('yes' or 'no')
	 * @param string $soundTalk Enable sound for Talk notifications ('yes' or 'no')
	 * @return DataResponse<Http::STATUS_OK, list<empty>, array{}>
	 *
	 * 200: Personal settings updated
	 */
	#[NoAdminRequired]
	#[OpenAPI]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion}/settings', requirements: ['apiVersion' => '(v2)'])]
	public function personal(int $batchSetting, string $soundNotification, string $soundTalk): DataResponse {
		$this->settingsMapper->setBatchSettingForUser($this->settingsMapper->getSettingsByUser($this->userId), $batchSetting);

		$this->config->setUserValue($this->userId, Application::APP_ID, 'sound_notification', $soundNotification !== 'no' ? 'yes' : 'no');
		$this->config->setUserValue($this->userId, Application::APP_ID, 'sound_talk', $soundTalk !== 'no' ? 'yes' : 'no');

		return new DataResponse();
	}

	/**
	 * Update default notification settings for new users
	 *
	 * @param int $batchSetting How often E-mails about missed notifications should be sent (hourly: 1; every three hours: 2; daily: 3; weekly: 4)
	 * @param string $soundNotification Enable sound for notifications ('yes' or 'no')
	 * @param string $soundTalk Enable sound for Talk notifications ('yes' or 'no')
	 * @return DataResponse<Http::STATUS_OK, list<empty>, array{}>
	 *
	 * 200: Admin settings updated
	 */
	#[OpenAPI(scope: OpenAPI::SCOPE_ADMINISTRATION)]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion}/settings/admin', requirements: ['apiVersion' => '(v2)'])]
	public function admin(int $batchSetting, string $soundNotification, string $soundTalk): DataResponse {
		$this->config->setAppValue(Application::APP_ID, 'setting_batchtime', (string)$batchSetting);
		$this->config->setAppValue(Application::APP_ID, 'sound_notification', $soundNotification !== 'no' ? 'yes' : 'no');
		$this->config->setAppValue(Application::APP_ID, 'sound_talk', $soundTalk !== 'no' ? 'yes' : 'no');

		return new DataResponse();
	}
}
