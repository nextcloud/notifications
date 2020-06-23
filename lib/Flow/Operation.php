<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
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

namespace OCA\Notifications\Flow;

use OCA\Notifications\Notifier\FlowNotifications;
use OCP\EventDispatcher\Event;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IManager;
use OCP\WorkflowEngine\EntityContext\IDisplayText;
use OCP\WorkflowEngine\EntityContext\IIcon;
use OCP\WorkflowEngine\EntityContext\IUrl;
use OCP\WorkflowEngine\IManager as FlowManager;
use OCP\WorkflowEngine\IOperation;
use OCP\WorkflowEngine\IRuleMatcher;
use UnexpectedValueException;

class Operation implements IOperation {

	/** @var IL10N */
	private $l;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IManager */
	private $notificationManager;
	/** @var IUserSession */
	private $userSession;

	public function __construct(
		IL10N $l,
		IURLGenerator $urlGenerator,
		IManager $notificationManager,
		IUserSession $userSession
	) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
		$this->notificationManager = $notificationManager;
		$this->userSession = $userSession;
	}

	/**
	 * @inheritDoc
	 */
	public function getDisplayName(): string {
		return $this->l->t('Sent a notification');
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): string {
		return $this->l->t('Triggers a notification');
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath('notifications', 'notifications.svg');
	}

	/**
	 * @inheritDoc
	 */
	public function isAvailableForScope(int $scope): bool {
		return $scope === FlowManager::SCOPE_USER;
	}

	/**
	 * @inheritDoc
	 */
	public function validateOperation(string $name, array $checks, string $operation): void {
		// pass
	}

	/**
	 * @inheritDoc
	 */
	public function onEvent(string $eventName, Event $event, IRuleMatcher $ruleMatcher): void {
		$flows = $ruleMatcher->getFlows(false);
		foreach ($flows as $flow) {
			try {
				$uid = $flow['scope_actor_id'];
				$sessionUser = $this->userSession->getUser();
				if ($sessionUser instanceof IUser && $uid ===$sessionUser->getUID()) {
					continue;
				}

				$entity = $ruleMatcher->getEntity();

				$notification = $this->notificationManager->createNotification();
				$notification->setApp(FlowNotifications::NOTIFIER_ID)
					->setIcon($entity instanceof IIcon ? $entity->getIconUrl() : $this->getIcon())
					->setUser($uid)
					->setSubject($entity->getName())
					->setObject($flow['entity'], '0')
					->setDateTime(new \DateTime());

				if ($entity instanceof IDisplayText) {
					$notification->setMessage($entity->getDisplayText(2));
				}
				if ($entity instanceof IUrl) {
					$notification->setLink($entity->getUrl());
				}

				$this->notificationManager->notify($notification);
			} catch (UnexpectedValueException $e) {
				continue;
			}
		}
	}
}
