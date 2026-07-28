<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

use OCA\Notifications\Vendor\Base64Url\Base64Url;
use OCA\Notifications\Vendor\Minishlink\WebPush\Subscription;
use OCA\Notifications\Vendor\Minishlink\WebPush\Utils;
use OCA\Notifications\Vendor\Minishlink\WebPush\VAPID;
use OCA\Notifications\Vendor\Minishlink\WebPush\WebPush;
use OCA\Notifications\WebPush\LoggerAdapter;
use OCP\AppFramework\Services\IAppConfig;
use Psr\Log\LoggerInterface;

class WebPushClient {
	/**
	 * Number of notifications that are sent as one pooled batch
	 */
	public const BATCH_SIZE = 1000;

	/**
	 * Number of requests that are in flight at the same time within a batch
	 */
	public const REQUEST_CONCURRENCY = 100;

	/**
	 * Timeout in seconds of a single request to a push endpoint.
	 * The library default of 30 seconds is way too long, as the sending
	 * blocks the process it runs in.
	 */
	public const REQUEST_TIMEOUT = 10;

	private WebPush $client;
	/** @psalm-var array{publicKey: string, privateKey: string, subject: string} */
	private array $vapid;

	public function __construct(
		protected IAppConfig $appConfig,
		protected LoggerInterface $logger,
	) {
		$this->vapid = $this->getVapid();
	}

	public static function isValidP256dh(string $key): bool {
		if (!preg_match('/^[A-Za-z0-9_-]{87}=*$/', $key)) {
			return false;
		}
		try {
			Utils::unserializePublicKey(Base64Url::decode($key));
		} catch (\InvalidArgumentException) {
			return false;
		}
		return true;
	}

	public static function isValidAuth(string $auth): bool {
		if (!preg_match('/^[A-Za-z0-9_-]{22}=*$/', $auth)) {
			return false;
		}
		try {
			$a = Base64Url::decode($auth);
		} catch (\InvalidArgumentException) {
			return false;
		}
		return strlen($a) === 16;
	}

	private function getClient(): WebPush {
		if (isset($this->client)) {
			return $this->client;
		}
		$this->client = new WebPush(
			auth: ['VAPID' => $this->vapid],
			defaultOptions: [
				'batchSize' => self::BATCH_SIZE,
				'requestConcurrency' => self::REQUEST_CONCURRENCY,
			],
			timeout: self::REQUEST_TIMEOUT,
			logger: new LoggerAdapter($this->logger),
		);
		$this->client->setReuseVAPIDHeaders(true);
		return $this->client;
	}

	/**
	 * @return array
	 * @psalm-return array{publicKey: string, privateKey: string, subject: string}
	 */
	private function getVapid(): array {
		// Do not use lazy for now
		try {
			$publicKey = $this->appConfig->getAppValueString('webpush_vapid_pubkey');
			$privateKey = $this->appConfig->getAppValueString('webpush_vapid_privkey');
		} catch (\Throwable) {
			// Decryption failed (e.g. mismatched instance secret), regenerate keys
			$publicKey = '';
			$privateKey = '';
		}
		if ($publicKey === '' || $privateKey === '') {
			/** @var array{publicKey: string, privateKey: string} $vapid */
			$vapid = VAPID::createVapidKeys();
			$this->appConfig->setAppValueString(
				'webpush_vapid_pubkey',
				$vapid['publicKey']
			);
			$this->appConfig->setAppValueString(
				'webpush_vapid_privkey',
				$vapid['privateKey'],
				sensitive: true
			);
		} else {
			$vapid = [
				'publicKey' => $publicKey,
				'privateKey' => $privateKey,
			];
		}
		$vapid['subject'] = 'https://nextcloud.com/contact/';
		return $vapid;
	}

	/**
	 * @return string
	 */
	public function getVapidPublicKey(): string {
		return $this->vapid['publicKey'];
	}

	/**
	 * Send one notification - blocking (should be avoided most of the time)
	 */
	public function notify(string $endpoint, string $uaPublicKey, string $auth, string $body): void {
		$c = $this->getClient();
		$c->queueNotification(
			new Subscription($endpoint, $uaPublicKey, $auth, 'aes128gcm'),
			$body
		);
		// the callback could be defined by the caller
		// For the moment, it is used during registration only - no need to catch 404 &co
		// as the registration isn't activated
		$callback = function ($r): void {
		};
		$c->flushPooled($callback);
	}

	/**
	 * Queue one notification. [flush] needs to be called to actually send the notifications
	 * @throws \ErrorException
	 */
	public function enqueue(string $endpoint, string $uaPublicKey, string $auth, string $body, string $urgency = 'normal'): void {
		$c = $this->getClient();
		$c->queueNotification(
			new Subscription($endpoint, $uaPublicKey, $auth, 'aes128gcm'),
			$body,
			options: [
				'urgency' => $urgency
			]
		);
	}

	/**
	 * @param callable $callback
	 * @psalm-param $callback callable(MessageSentReport): void
	 */
	public function flush(callable $callback): void {
		$c = $this->getClient();
		$c->flushPooled($callback);
	}
}
