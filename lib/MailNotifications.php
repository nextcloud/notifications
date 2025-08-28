<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Defaults;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Mail\IMailer;
use OCP\Mail\IMessage;
use OCP\Notification\AlreadyProcessedException;
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\IncompleteParsedNotificationException;
use OCP\Notification\INotification;
use OCP\Util;
use Psr\Log\LoggerInterface;

class MailNotifications {
	public const BATCH_SIZE_CLI = 500;
	public const BATCH_SIZE_WEB = 25;

	public function __construct(
		protected IConfig $config,
		protected IAppConfig $appConfig,
		protected IManager $manager,
		protected Handler $handler,
		protected IUserManager $userManager,
		protected LoggerInterface $logger,
		protected IMailer $mailer,
		protected IURLGenerator $urlGenerator,
		protected Defaults $defaults,
		protected IFactory $l10nFactory,
		protected IDateTimeFormatter $dateFormatter,
		protected ITimeFactory $timeFactory,
		protected SettingsMapper $settingsMapper,
	) {
	}

	/**
	 * Send all due notification emails.
	 *
	 * @param int $batchSize
	 * @param int $sendTime
	 */
	public function sendEmails(int $batchSize, int $sendTime): void {
		$userSettings = $this->settingsMapper->getUsersByNextSendTime($batchSize);

		if (empty($userSettings)) {
			return;
		}

		$userIds = array_map(static fn (Settings $settings) => $settings->getUserId(), $userSettings);

		// Batch-read settings
		$fallbackTimeZone = date_default_timezone_get();
		/** @psalm-var array<string, string> $userTimezones */
		$userTimezones = $this->config->getUserValueForUsers('core', 'timezone', $userIds);
		$userEnabled = $this->config->getUserValueForUsers('core', 'enabled', $userIds);
		$defaultBatchTime = SettingsMapper::batchSettingToTime($this->appConfig->getAppValueInt('setting_batchtime'));

		$fallbackLang = $this->config->getSystemValue('force_language', null);
		if (is_string($fallbackLang)) {
			/** @psalm-var array<string, string> $userLanguages */
			$userLanguages = [];
		} else {
			$fallbackLang = $this->config->getSystemValueString('default_language', 'en');
			/** @psalm-var array<string, string> $userLanguages */
			$userLanguages = $this->config->getUserValueForUsers('core', 'lang', $userIds);
		}

		foreach ($userSettings as $settings) {
			$batchTime = $settings->getBatchTime();
			if ($batchTime === Settings::EMAIL_SEND_DEFAULT) {
				$batchTime = $defaultBatchTime;
			}

			$userId = $settings->getUserId();
			if (isset($userEnabled[$userId]) && $userEnabled[$userId] === 'false') {
				// User is disabled, skip sending the email for them
				if ($settings->getNextSendTime() <= $sendTime) {
					$settings->setNextSendTime(
						$sendTime + $batchTime
					);
					$this->settingsMapper->update($settings);
				}
				continue;
			}

			// Get the settings for this particular user, then check if we have notifications to email them
			$languageCode = $userLanguages[$userId] ?? $fallbackLang;
			$timezone = $userTimezones[$userId] ?? $fallbackTimeZone;

			/** @var array<int, INotification> $notifications */
			$notifications = $this->handler->getAfterId($settings->getLastSendId(), $userId);
			if (!empty($notifications)) {
				$oldestNotification = end($notifications);
				$shouldSendAfter = $oldestNotification->getDateTime()->getTimestamp() + $batchTime;

				if ($shouldSendAfter <= $sendTime) {
					// User has notifications that should send
					$this->sendEmailToUser($settings, $notifications, $languageCode, $timezone, $batchTime);
				} else {
					// User has notifications but we didn't reach the timeout yet,
					// So delay sending to the time of the notification + batch setting
					$settings->setNextSendTime($shouldSendAfter);
					$this->settingsMapper->update($settings);
				}
			} else {
				$settings->setNextSendTime($sendTime + $batchTime);
				$this->settingsMapper->update($settings);
			}
		}
	}

	/**
	 * Send an email to the user containing given list of notifications
	 *
	 * @param Settings $settings
	 * @param non-empty-array<int, INotification> $notifications
	 * @param string $language
	 * @param string $timezone
	 */
	protected function sendEmailToUser(Settings $settings, array $notifications, string $language, string $timezone, int $batchTime): void {
		$lastSendId = array_key_first($notifications);
		$lastSendTime = $this->timeFactory->getTime();

		$preparedNotifications = [];
		foreach ($notifications as $notification) {
			/** @var INotification $preparedNotification */
			try {
				$preparedNotification = $this->manager->prepare($notification, $language);
			} catch (AlreadyProcessedException|IncompleteParsedNotificationException|\InvalidArgumentException) {
				// FIXME remove \InvalidArgumentException in Nextcloud 39
				// The app was disabled, skip the notification
				continue;
			} catch (\Exception $e) {
				$this->logger->error($e->getMessage(), [
					'exception' => $e,
				]);
				continue;
			}

			$preparedNotifications[] = $preparedNotification;
		}

		if (count($preparedNotifications) > 0) {
			$message = $this->prepareEmailMessage($settings->getUserId(), $preparedNotifications, $language, $timezone);

			if ($message !== null) {
				try {
					$this->mailer->send($message);
				} catch (\Exception $e) {
					$this->logger->error($e->getMessage(), [
						'exception' => $e,
					]);
					return;
				}

				$settings->setLastSendId($lastSendId);
				$settings->setNextSendTime($lastSendTime + $batchTime);
				$this->settingsMapper->update($settings);
			}
		}
	}

	/**
	 * prepare the contents of the email message containing the provided list of notifications
	 *
	 * @param string $uid
	 * @param INotification[] $notifications
	 * @param string $language
	 * @param string $timezone
	 * @return ?IMessage message contents
	 */
	protected function prepareEmailMessage(string $uid, array $notifications, string $language, string $timezone): ?IMessage {
		$user = $this->userManager->get($uid);
		if (!$user instanceof IUser) {
			return null;
		}

		$userEmailAddress = $user->getEMailAddress();
		if (empty($userEmailAddress)) {
			return null;
		}

		// Prepare our email template
		$l10n = $this->l10nFactory->get('notifications', $language);

		$userDisplayName = $user->getDisplayName();
		$absoluteUrl = $this->urlGenerator->getAbsoluteURL('/');
		$instanceName = $this->defaults->getName();

		$template = $this->mailer->createEMailTemplate('notifications.EmailNotification', [
			'displayname' => $userDisplayName,
			'url' => $absoluteUrl
		]);

		// Prepare email header
		$template->addHeader();
		$template->addHeading($l10n->t('Hello %s', [$userDisplayName]), $l10n->t('Hello %s,', [$userDisplayName]));

		// Prepare email subject and body mentioning amount of notifications
		$homeLink = '<a href="' . $absoluteUrl . '">' . htmlspecialchars($instanceName) . '</a>';
		$notificationsCount = count($notifications);
		$template->setSubject($l10n->n('New notification for %s', '%n new notifications for %s', $notificationsCount, [$instanceName]));
		$template->addBodyText(
			$l10n->n('You have a new notification for %s', 'You have %n new notifications for %s', $notificationsCount, [$homeLink]),
			$l10n->n('You have a new notification for %s', 'You have %n new notifications for %s', $notificationsCount, [$absoluteUrl])
		);

		// Prepare email body with the content of missed notifications
		// Notifications are assumed to be passed-in in descending order (latest first). Reversing to present chronologically.
		$notifications = array_reverse($notifications);

		foreach ($notifications as $notification) {
			try {
				$relativeDateTime = $this->dateFormatter->formatDateTimeRelativeDay(
					$notification->getDateTime(),
					'long',
					'short',
					new \DateTimeZone($timezone ?: 'UTC'),
					$l10n
				);
				$template->addBodyListItem($this->getHTMLContents($notification), $relativeDateTime, $notification->getIcon(), $notification->getParsedSubject());

				// Buttons probably were not intended for this, but it works ok enough for showing the idea.
				$actions = $notification->getParsedActions();
				foreach ($actions as $action) {
					if ($action->getRequestType() === IAction::TYPE_WEB) {
						$template->addBodyButton($action->getParsedLabel(), $action->getLink());
					}
				}
			} catch (\Throwable $e) {
				$this->logger->error(
					'An error occurred while preparing a notification ('
					. $notification->getApp() . '|' . $notification->getSubject()
					. '|' . $notification->getObjectType() . '|' . $notification->getObjectId()
					. ') for sending',
					['exception' => $e]
				);
				return null;
			}
		}

		// Prepare email footer
		$linkToPersonalSettings = $this->urlGenerator->linkToRouteAbsolute('settings.PersonalSettings.index', ['section' => 'notifications']);
		$template->addBodyText(
			$l10n->t('You can change the frequency of these emails or disable them in the <a href="%s">settings</a>.', $linkToPersonalSettings),
			$l10n->t('You can change the frequency of these emails or disable them in the settings: %s', $linkToPersonalSettings)
		);

		$template->addFooter();

		$message = $this->mailer->createMessage();
		$message->useTemplate($template);
		$message->setTo([$userEmailAddress => $userDisplayName]);
		$message->setFrom([Util::getDefaultEmailAddress('no-reply') => $instanceName]);

		return $message;
	}

	/**
	 * return HTML to display this notification
	 *
	 * @param INotification $notification
	 * @return string
	 */
	protected function getHTMLContents(INotification $notification): string {
		$HTMLSubject = $this->getHTMLSubject($notification);
		$link = $notification->getLink();
		if ($link !== '') {
			$HTMLSubject = '<a href="' . $link . '">' . $HTMLSubject . '</a>';
		}

		return $HTMLSubject . '<br>' . $this->getHTMLMessage($notification);
	}

	/**
	 * return HTML to display the subject of this notification
	 *
	 * @param INotification $notification
	 * @return string
	 */
	protected function getHTMLSubject(INotification $notification): string {
		$contentString = htmlspecialchars($notification->getRichSubject());
		if ($contentString === '') {
			return htmlspecialchars($notification->getParsedSubject());
		}

		return $this->replaceRichParameters($notification->getRichSubjectParameters(), $contentString);
	}

	/**
	 * return HTML to display the message body of this notification
	 *
	 * @param INotification $notification
	 * @return string
	 */
	protected function getHTMLMessage(INotification $notification): string {
		$contentString = htmlspecialchars($notification->getRichMessage());
		if ($contentString === '') {
			return htmlspecialchars($notification->getParsedMessage());
		}

		return $this->replaceRichParameters($notification->getRichMessageParameters(), $contentString);
	}

	/**
	 * replace the given parameters in the input content string for display in an email
	 *
	 * @param array<string, array<string, string>> $parameters
	 * @param string $contentString
	 * @return string $contentString with parameters processed
	 */
	protected function replaceRichParameters(array $parameters, string $contentString): string {
		$placeholders = $replacements = [];
		foreach ($parameters as $placeholder => $parameter) {
			$placeholders[] = '{' . $placeholder . '}';

			if ($parameter['type'] === 'file') {
				$replacement = $parameter['path'];
			} else {
				$replacement = $parameter['name'];
			}

			if (isset($parameter['link'])) {
				$replacements[] = '<a href="' . $parameter['link'] . '">' . htmlspecialchars($replacement) . '</a>';
			} else {
				$replacements[] = '<strong>' . htmlspecialchars($replacement) . '</strong>';
			}
		}

		return str_replace($placeholders, $replacements, $contentString);
	}
}
