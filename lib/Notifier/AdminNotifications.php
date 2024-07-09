<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Notifier;

use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\IAction;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\UnknownNotificationException;

class AdminNotifications implements INotifier {
	public function __construct(
		protected IFactory $l10nFactory,
		protected IURLGenerator $urlGenerator,
		protected IUserManager $userManager,
		protected IRootFolder $rootFolder,
	) {
	}

	/**
	 * Identifier of the notifier, only use [a-z0-9_]
	 *
	 * @return string
	 * @since 17.0.0
	 */
	public function getID(): string {
		return 'admin_notifications';
	}

	/**
	 * Human-readable name describing the notifier
	 *
	 * @return string
	 * @since 17.0.0
	 */
	public function getName(): string {
		return $this->l10nFactory->get('notifications')->t('Admin notifications');
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 * @return INotification
	 * @throws UnknownNotificationException When the notification was not prepared by a notifier
	 */
	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== 'admin_notifications' && $notification->getApp() !== 'admin_notification_talk') {
			throw new UnknownNotificationException('app');
		}

		switch ($notification->getSubject()) {
			case 'dummy':
				$subjectParams = $notification->getSubjectParameters();
				$numActions = (int) $subjectParams[0];

				$user = $this->userManager->get($notification->getUser());
				assert($user instanceof IUser);
				$userFolder = $this->rootFolder->getUserFolder($user->getUID());
				$dirList = $userFolder->getDirectoryListing();
				if (empty($dirList)) {
					$file1 = $userFolder;
				} else {
					$file1 = array_pop($dirList);
				}
				if (empty($dirList)) {
					$file2 = $userFolder;
				} else {
					$file2 = array_shift($dirList);
					if ($file2 instanceof Folder) {
						$dirList = $file2->getDirectoryListing();
						if (!empty($dirList)) {
							$file2 = array_shift($dirList);
						}
					}
				}

				$path1 = rtrim($file1->getPath(), '/');
				if (strpos($path1, '/' . $notification->getUser() . '/files/') === 0) {
					// Remove /user/files/...
					[,,, $path1] = explode('/', $path1, 4);
				}
				$path2 = rtrim($file2->getPath(), '/');
				if (strpos($path2, '/' . $notification->getUser() . '/files/') === 0) {
					// Remove /user/files/...
					[,,, $path2] = explode('/', $path2, 4);
				}

				$loremIpsum = 'User {actor} owns a file {item}';
				$loremIpsumLong = 'Lorem {user-2} dolor sit {file-3}, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.' . "\n" . 'Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';

				$notification->setRichSubject($loremIpsum, [
					'actor' => [
						'type' => 'user',
						'id' => $user->getUID(),
						'name' => $user->getDisplayName(),
					],
					'item' => [
						'type' => 'file',
						'id' => $file1->getId(),
						'name' => $file1->getName(),
						'size' => $file1->getSize(),
						'path' => $path1,
						'link' => $this->urlGenerator->linkToRouteAbsolute('files.viewcontroller.showFile', ['fileid' => $file1->getId()]),
						'mimetype' => $file1->getMimetype(),
					],
				]);
				$notification->setRichMessage($loremIpsumLong, [
					'user-2' => [
						'type' => 'user',
						'id' => $user->getUID(),
						'name' => $user->getDisplayName(),
					],
					'file-3' => [
						'type' => 'file',
						'id' => $file2->getId(),
						'name' => $file2->getName(),
						'size' => $file2->getSize(),
						'path' => $path2,
						'link' => $this->urlGenerator->linkToRouteAbsolute('files.viewcontroller.showFile', ['fileid' => $file2->getId()]),
						'mimetype' => $file2->getMimetype(),
					],
				]);

				$primary = $notification->createAction();
				$primary->setPrimary(true);
				$primary->setParsedLabel('3 is prim(e|ary)');
				$primary->setLink(
					'https://en.wikipedia.org/wiki/3#Mathematics',
					IAction::TYPE_WEB
				);

				$secondary = $notification->createAction();
				$secondary->setPrimary(false);
				$secondary->setParsedLabel('Get status');
				$secondary->setLink(
					$this->urlGenerator->getAbsoluteURL('status.php'),
					IAction::TYPE_GET
				);

				$three = $notification->createAction();
				$three->setPrimary(false);
				$three->setParsedLabel('Delete status.php');
				$three->setLink(
					$this->urlGenerator->getAbsoluteURL('status.php'),
					IAction::TYPE_DELETE
				);

				$numActions = min(3, $numActions);
				switch ($numActions) {
					case 3:
						$notification->addParsedAction($three);
						// no break
					case 2:
						$notification->addParsedAction($secondary);
						// no break
					case 1:
						$notification->addParsedAction($primary);
				}

				$notification->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('notifications', 'notifications-dark.svg')));
				return $notification;

				// Deal with known subjects
			case 'cli':
			case 'ocs':
				$subjectParams = $notification->getSubjectParameters();
				if ($subjectParams['parsed'] !== '') {
					$notification->setParsedSubject($subjectParams['parsed']);
				}
				if ($subjectParams['rich'] !== '') {
					$notification->setRichSubject($subjectParams['rich'], $subjectParams['parameters']);
				}
				$messageParams = $notification->getMessageParameters();
				if (!empty($messageParams)) {
					if ($messageParams['parsed'] !== '') {
						$notification->setParsedMessage($messageParams['parsed']);
					}
					if ($messageParams['rich'] !== '') {
						$notification->setRichMessage($messageParams['rich'], $messageParams['parameters']);
					}
				}

				$notification->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('notifications', 'notifications-dark.svg')));
				return $notification;

			default:
				throw new UnknownNotificationException('subject');
		}
	}
}
