<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications\Tests\Unit\Controller;

use NCU\AppFramework\Http\Attribute\TokenScope;
use OCA\Notifications\Controller\APIController;
use OCA\Notifications\Controller\EndpointController;
use OCA\Notifications\Controller\PushController;
use OCA\Notifications\Controller\SettingsController;
use OCA\Notifications\Controller\WebPushController;
use OCA\Notifications\TokenScopes;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\TestCase;

class TokenScopeTest extends TestCase {
	public static function openControllers(): array {
		return [
			'notifications' => [EndpointController::class],
			'push registration' => [PushController::class],
		];
	}

	#[DataProvider('openControllers')]
	public function testOpenControllers(string $class): void {
		$attribute = (new \ReflectionClass($class))->getAttributes(TokenScope::class)[0];
		$this->assertSame(TokenScopes::NOTIFICATIONS_READ, $attribute->getArguments()['scope']);
	}

	public static function closedControllers(): array {
		return [
			'admin' => [APIController::class],
			'settings' => [SettingsController::class],
			'web push' => [WebPushController::class],
		];
	}

	#[DataProvider('closedControllers')]
	public function testClosedControllers(string $class): void {
		$class = new \ReflectionClass($class);
		$this->assertSame([], $class->getAttributes(TokenScope::class));
		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			$this->assertSame([], $method->getAttributes(TokenScope::class), $method->getName());
		}
	}
}
