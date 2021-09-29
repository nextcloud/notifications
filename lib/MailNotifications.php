<?php

declare(strict_types=1);

/**
 * @author Julien Barnoin <julien@barnoin.com>
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

namespace OCA\Notifications;

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
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\Notification\IAction;
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

	public const DEFAULT_BATCH_TIME = 3600 * 24;

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
		ITimeFactory $timeFactory
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
	}

	/**
	 * Send all due notification emails.
	 */
	public function sendEmails(): void {
		// Get all users who enabled notification emails.
		$users = $this->config->getUsersForUserValue('notifications', 'notifications_email_enabled', '1');

		if (count($users) == 0) {
			return;
		}

		// Batch-read settings that will be used to figure out who needs notifications sent out
		$userBatchTimes = $this->config->getUserValueForUsers('notifications', 'notify_setting_batchtime', $users);
		$userLastSendIds = $this->config->getUserValueForUsers('notifications', 'mail_last_send_id', $users);
		$userLastSendTimes = $this->config->getUserValueForUsers('notifications', 'mail_last_send_time', $users);

		$now = $this->timeFactory->getTime();

		foreach ($users as $user) {
			// Get the settings for this particular user, then check if we have notifications to email them
			$batchTime = (int) ($userBatchTimes[$user] ?? self::DEFAULT_BATCH_TIME);
			$lastSendId = (int) ($userLastSendIds[$user] ?? -1);
			$lastSendTime = (int) ($userLastSendTimes[$user] ?? -1);

			if (($now - $lastSendTime) >= $batchTime) {
				// Enough time passed since last send for the user's desired interval between mails.
				$notifications = $this->handler->getAfterId($lastSendId, $user);
				if (!empty($notifications)) {
					$this->sendEmailToUser($user, $notifications, $now);
				}
			}
		}
	}

	/**
	 * send an email to the user containing given list of notifications
	 *
	 * @param string $uid
	 * @param INotification[] $notifications
	 * @param int $now
	 */
	protected function sendEmailToUser(string $uid, array $notifications, int $now): void {
		$lastSendId = array_key_first($notifications);

		$language = $this->config->getUserValue($uid, 'core', 'lang', $this->config->getSystemValue('default_language', 'en'));

		$preparedNotifications = [];
		foreach ($notifications as $notification) {
			/** @var INotification $preparedNotification */
			try {
				$preparedNotification = $this->manager->prepare($notification, $language);
			} catch (\InvalidArgumentException $e) {
				// The app was disabled, skip the notification
				continue;
			} catch (\Exception $e) {
				$this->logger->error($e->getMessage());
				continue;
			}

			$preparedNotifications[] = $preparedNotification;
		}

		if (count($preparedNotifications) > 0) {
			$message = $this->prepareEmailMessage($uid, $preparedNotifications, $language);

			if ($message != null) {
				try {
					$this->mailer->send($message);

					// This is handled in config values based on how 'activity_digest_last_send' works,
					//  but it would likely be a better choice to have this stored in a db like the activity mail queue?
					$this->config->setUserValue($uid, 'notifications', 'mail_last_send_id', (string)$lastSendId);
					$this->config->setUserValue($uid, 'notifications', 'mail_last_send_time', (string)$now);
				} catch (\Exception $e) {
					$this->logger->error($e->getMessage());
				}
			}
		}
	}

	/**
	 * prepare the contents of the email message containing the provided list of notifications
	 *
	 * @param string $uid
	 * @param INotification[] $notifications
	 * @param string $language
	 * @return ?IMessage message contents
	 */
	protected function prepareEmailMessage(string $uid, array $notifications, string $language): ?IMessage {
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
		$template->addHeading($l10n->t('Hello %s',[$user->getDisplayName()]), $l10n->t('Hello %s,',[$user->getDisplayName()]));

		// Prepare email subject and body mentioning amount of notifications
		$homeLink = '<a href="' . $this->urlGenerator->getAbsoluteURL('/') . '">' . htmlspecialchars($this->defaults->getName()) . '</a>';
		$notificationsCount = count($notifications);
		$template->setSubject($l10n->n('New notification for %s', '%n new notifications for %s', $notificationsCount, [$this->defaults->getName()]));
		$template->addBodyText(
			$l10n->n('You have a new notification for %s', 'You have %n new notifications for %s', $notificationsCount, [$homeLink]),
			$l10n->n('You have a new notification for %s', 'You have %n new notifications for %s', $notificationsCount, [$this->urlGenerator->getAbsoluteURL('/')])
		);

		// Prepare email body with the content of missed notifications
		// Notifications are assumed to be passed in in descending order (latest first). Reversing to present chronologically.
		$notifications = array_reverse($notifications);

		$timezone = $this->config->getUserValue($uid, 'core', 'timezone', date_default_timezone_get());

		foreach ($notifications as $notification) {
			$relativeDateTime = $this->dateFormatter->formatDateTimeRelativeDay($notification->getDateTime(), 'long', 'short', new \DateTimeZone($timezone), $l10n);
			$template->addBodyListItem($this->getHTMLContents($notification), $relativeDateTime, $notification->getIcon(), $notification->getParsedSubject());

			// Buttons probably were not intended for this, but it works ok enough for showing the idea.
			$actions = $notification->getParsedActions();
			foreach ($actions as $action) {
				if ($action->getRequestType() === IAction::TYPE_WEB) {
					$template->addBodyButton($action->getLabel(), $action->getLink());
				}
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
