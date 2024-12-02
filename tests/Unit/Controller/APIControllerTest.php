<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit\Controller;

use OCA\Notifications\App;
use OCA\Notifications\Controller\APIController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Notification\IManager;
use OCP\Notification\IncompleteNotificationException;
use OCP\Notification\INotification;
use OCP\RichObjectStrings\IValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * Class APIControllerTest
 *
 * @package OCA\Notifications\Tests\Unit\Controller
 * @group DB
 */
class APIControllerTest extends TestCase {
	protected ITimeFactory&MockObject $timeFactory;
	protected IUserManager&MockObject $userManager;
	protected IUserSession&MockObject $userSession;
	protected IManager&MockObject $notificationManager;
	protected App&MockObject $notificationApp;
	protected IValidator&MockObject $richValidator;
	protected IL10N&MockObject $l;
	protected LoggerInterface&MockObject $logger;

	protected APIController $controller;

	protected function setUp(): void {
		parent::setUp();

		/** @var IRequest|\PHPUnit_Framework_MockObject_MockObject $request */
		$request = $this->createMock(IRequest::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->notificationManager = $this->createMock(IManager::class);
		$this->notificationApp = $this->createMock(App::class);
		$this->richValidator = $this->createMock(IValidator::class);
		$this->l = $this->createMock(IL10N::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->controller = new APIController(
			'notifications',
			$request,
			$this->timeFactory,
			$this->userManager,
			$this->userSession,
			$this->notificationManager,
			$this->notificationApp,
			$this->richValidator,
			$this->l,
			$this->logger,
		);
	}

	public static function dataGenerateNotification(): array {
		return [
			['user', '', '', false, null, false, null, 123, null, Http::STATUS_NOT_FOUND],
			['user', '', '', false, null, false, 'user', 123, null, Http::STATUS_BAD_REQUEST],
			['user', str_repeat('a', 256), '', false, null, false, 'user', 123, null, Http::STATUS_BAD_REQUEST],
			['user', 'short', '', true, false, false, 'user', 123, '7b', Http::STATUS_OK],
			['user', 'short', str_repeat('a', 4001), false, null, false, 'user', 123, null, Http::STATUS_BAD_REQUEST],
			['user', 'short', str_repeat('a', 4000), true, false, true, 'user', 123, '7b', Http::STATUS_OK],
			['user', 'short', 'long', true, true, true, 'user', 123, '7b', Http::STATUS_INTERNAL_SERVER_ERROR],
		];
	}

	/**
	 * @dataProvider dataGenerateNotification
	 */
	public function testGenerateNotification(string $userId, string $short, string $long, bool $createNotification, ?bool $notifyThrows, bool $validLong, ?string $user, int $time, ?string $hexTime, int $statusCode): void {
		if ($user !== null) {
			$u = $this->createMock(IUser::class);
			$u->expects($createNotification ? $this->once() : $this->never())
				->method('getUID')
				->willReturn($user);
		} else {
			$u = null;
		}
		$this->userManager->expects($this->any())
			->method('get')
			->with($userId)
			->willReturn($u);

		$dateTime = new \DateTime();
		$dateTime->setTimestamp($time);
		$this->timeFactory->expects($hexTime === null ? $this->never() : $this->once())
			->method('getDateTime')
			->willReturn($dateTime);

		if ($createNotification) {
			$n = $this->createMock(INotification::class);
			$n->expects($this->once())
				->method('setApp')
				->with('admin_notifications')
				->willReturnSelf();
			$n->expects($this->once())
				->method('setUser')
				->with($user)
				->willReturnSelf();
			$n->expects($this->once())
				->method('setDateTime')
				->willReturnSelf();
			$n->expects($this->once())
				->method('setObject')
				->with('admin_notifications', $hexTime)
				->willReturnSelf();
			$n->expects($this->once())
				->method('setSubject')
				->with('ocs', ['subject' => $short, 'parameters' => []])
				->willReturnSelf();
			if ($validLong) {
				$n->expects($this->once())
					->method('setMessage')
					->with('ocs', ['message' => $long, 'parameters' => []])
					->willReturnSelf();
			} else {
				$n->expects($this->never())
					->method('setMessage');
			}

			$this->notificationManager->expects($this->once())
				->method('createNotification')
				->willReturn($n);

			if ($notifyThrows === null) {
				$this->notificationManager->expects($this->never())
					->method('notify');
			} elseif ($notifyThrows === false) {
				$this->notificationManager->expects($this->once())
					->method('notify')
					->with($n);
			} elseif ($notifyThrows === true) {
				$this->notificationManager->expects($this->once())
					->method('notify')
					->willThrowException(new IncompleteNotificationException());
			}
		}

		$response = $this->controller->generateNotification($userId, $short, $long);

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame($statusCode, $response->getStatus());
	}
}
