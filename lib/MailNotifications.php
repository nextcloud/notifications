<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021, Julien Barnoin <julien@barnoin.com>
 *
 * @author Julien Barnoin <julien@barnoin.com>
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

namespace OCA\Notifications;

use OCA\Notifications\Model\Settings;
use OCA\Notifications\Model\SettingsMapper;
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
use OCP\Notification\IAction;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\Util;
use Psr\Log\LoggerInterface;

class MailNotifications {
	/** @var IConfig */
	private $config;

	/** @var IManager */
	private $manager;

	/** @var Handler */
	protected $handler;

	/** @var IUserManager */
	private $userManager;

	/** @var LoggerInterface */
	private $logger;

	/** @var IMailer */
	private $mailer;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var Defaults */
	private $defaults;

	/** @var IFactory */
	private $l10nFactory;

	/** @var IDateTimeFormatter */
	private $dateFormatter;

	/** @var ITimeFactory */
	protected $timeFactory;

	/** @var SettingsMapper */
	protected $settingsMapper;

	public const BATCH_SIZE_CLI = 500;
	public const BATCH_SIZE_WEB = 25;

	public function __construct(
		IConfig $config,
		IManager $manager,
		Handler $handler,
		IUserManager $userManager,
		LoggerInterface $logger,
		IMailer $mailer,
		IURLGenerator $urlGenerator,
		Defaults $defaults,
		IFactory $l10nFactory,
		IDateTimeFormatter $dateTimeFormatter,
		ITimeFactory $timeFactory,
		SettingsMapper $settingsMapper
	) {
		$this->config = $config;
		$this->manager = $manager;
		$this->handler = $handler;
		$this->userManager = $userManager;
		$this->logger = $logger;
		$this->mailer = $mailer;
		$this->urlGenerator = $urlGenerator;
		$this->defaults = $defaults;
		$this->l10nFactory = $l10nFactory;
		$this->dateFormatter = $dateTimeFormatter;
		$this->timeFactory = $timeFactory;
		$this->settingsMapper = $settingsMapper;
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

		$userIds = array_map(static function (Settings $settings) {
			return $settings->getUserId();
		}, $userSettings);

		// Batch-read settings
		$fallbackTimeZone = date_default_timezone_get();
		$userTimezones = $this->config->getUserValueForUsers('core', 'timezone', $userIds);
		$userEnabled = $this->config->getUserValueForUsers('core', 'enabled', $userIds);

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
			if (isset($userEnabled[$settings->getUserId()]) && $userEnabled[$settings->getUserId()] === 'false') {
				// User is disabled, skip sending the email for them
				if ($settings->getNextSendTime() <= $sendTime) {
					$settings->setNextSendTime(
						$sendTime + $settings->getBatchTime()
					);
					$this->settingsMapper->update($settings);
				}
				continue;
			}

			// Get the settings for this particular user, then check if we have notifications to email them
			$languageCode = $userLanguages[$settings->getUserId()] ?? $fallbackLang;
			$timezone = $userTimezones[$settings->getUserId()] ?? $fallbackTimeZone;

			/** @var INotification[] $notifications */
			$notifications = $this->handler->getAfterId($settings->getLastSendId(), $settings->getUserId());
			if (!empty($notifications)) {
				$oldestNotification = end($notifications);
				$shouldSendAfter = $oldestNotification->getDateTime()->getTimestamp() + $settings->getBatchTime();

				if ($shouldSendAfter <= $sendTime) {
					// User has notifications that should send
					$this->sendEmailToUser($settings, $notifications, $languageCode, $timezone);
				} else {
					// User has notifications but we didn't reach the timeout yet,
					// So delay sending to the time of the notification + batch setting
					$settings->setNextSendTime($shouldSendAfter);
					$this->settingsMapper->update($settings);
				}
			} else {
				$settings->setNextSendTime($sendTime + $settings->getBatchTime());
				$this->settingsMapper->update($settings);
			}
		}
	}

	/**
	 * send an email to the user containing given list of notifications
	 *
	 * @param Settings $settings
	 * @param INotification[] $notifications
	 * @param string $language
	 * @param string $timezone
	 */
	protected function sendEmailToUser(Settings $settings, array $notifications, string $language, string $timezone): void {
		$lastSendId = array_key_first($notifications);
		$lastSendTime = $this->timeFactory->getTime();

		$preparedNotifications = [];
		foreach ($notifications as $notification) {
			/** @var INotification $preparedNotification */
			try {
				$preparedNotification = $this->manager->prepare($notification, $language);
			} catch (\InvalidArgumentException $e) {
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
				$settings->setNextSendTime($lastSendTime + $settings->getBatchTime());
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

		$template = $this->mailer->createEMailTemplate('notifications.EmailNotification', [
			'displayname' => $user->getDisplayName(),
			'url' => $this->urlGenerator->getAbsoluteURL('/')
		]);

		// Prepare email header
		$template->addHeader();
		$template->addHeading($l10n->t('Hello %s', [$user->getDisplayName()]), $l10n->t('Hello %s,', [$user->getDisplayName()]));

		// Prepare email subject and body mentioning amount of notifications
		$homeLink = '<a href="' . $this->urlGenerator->getAbsoluteURL('/') . '">' . htmlspecialchars($this->defaults->getName()) . '</a>';
		$notificationsCount = count($notifications);
		$template->setSubject($l10n->n('New notification for %s', '%n new notifications for %s', $notificationsCount, [$this->defaults->getName()]));
		$template->addBodyText(
			$l10n->n('You have a new notification for %s', 'You have %n new notifications for %s', $notificationsCount, [$homeLink]),
			$l10n->n('You have a new notification for %s', 'You have %n new notifications for %s', $notificationsCount, [$this->urlGenerator->getAbsoluteURL('/')])
		);

		// Prepare email body with the content of missed notifications
		// Notifications are assumed to be passed-in in descending order (latest first). Reversing to present chronologically.
		$notifications = array_reverse($notifications);

		foreach ($notifications as $notification) {
			try {
				$relativeDateTime = $this->dateFormatter->formatDateTimeRelativeDay($notification->getDateTime(), 'long', 'short', new \DateTimeZone($timezone), $l10n);
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
		$template->addBodyText(
			$l10n->t('You can change the frequency of these emails or disable them in the <a href="%s">settings</a>.', $this->urlGenerator->linkToRouteAbsolute('settings.PersonalSettings.index', ['section' => 'notifications'])),
			$l10n->t('You can change the frequency of these emails or disable them in the settings: %s', $this->urlGenerator->linkToRouteAbsolute('settings.PersonalSettings.index', ['section' => 'notifications']))
		);

		$template->addFooter();

		$message = $this->mailer->createMessage();
		$message->useTemplate($template);
		$message->setTo([$userEmailAddress => $user->getDisplayName()]);
		$message->setFrom([Util::getDefaultEmailAddress('no-reply') => $this->defaults->getName()]);

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
	 * @param array [string => string] $parameters
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
