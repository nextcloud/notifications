<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2017, Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OCA\Notifications\Notifier;

use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\AlreadyProcessedException;
use OCP\Notification\IAction;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class AdminNotifications implements INotifier {

	/** @var IFactory */
	protected $l10nFactory;

	/** @var IURLGenerator */
	protected $urlGenerator;
	/** @var IUserManager */
	protected $userManager;
	/** @var IRootFolder */
	protected $rootFolder;

	public function __construct(IFactory $l10nFactory,
								IURLGenerator $urlGenerator,
								IUserManager $userManager,
								IRootFolder $rootFolder) {
		$this->l10nFactory = $l10nFactory;
		$this->urlGenerator = $urlGenerator;
		$this->userManager = $userManager;
		$this->rootFolder = $rootFolder;
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
	 * @throws \InvalidArgumentException When the notification was not prepared by a notifier
	 * @throws AlreadyProcessedException When the notification is not needed anymore and should be deleted
	 */
	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== 'admin_notifications' && $notification->getApp() !== 'admin_notification_talk') {
			throw new \InvalidArgumentException('Unknown app');
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
				$notification->setParsedSubject($subjectParams[0]);
				$messageParams = $notification->getMessageParameters();
				if (isset($messageParams[0]) && $messageParams[0] !== '') {
					$notification->setParsedMessage($messageParams[0]);
				}

				$notification->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('notifications', 'notifications-dark.svg')));
				return $notification;

			default:
				throw new \InvalidArgumentException('Unknown subject');
		}
	}
}
