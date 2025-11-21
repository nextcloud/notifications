<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

use OCA\Notifications\Vendor\Base64Url\Base64Url;
use OCA\Notifications\Vendor\Minishlink\WebPush\MessageSentReport;
use OCA\Notifications\Vendor\Minishlink\WebPush\Subscription;
use OCA\Notifications\Vendor\Minishlink\WebPush\Utils;
use OCA\Notifications\Vendor\Minishlink\WebPush\WebPush;
use Psr\Log\LoggerInterface;

class WebPushClient {
    private WebPush $client;

    public function __construct(
        protected LoggerInterface $log,
    ) {}

    static public function isValidP256dh(string $key): bool {
		if (!preg_match('/^[A-Za-z0-9_-]{87}=*$/', $key)) {
		    return false;
	    }
	    try {
	        Utils::unserializePublicKey(Base64Url::decode($key));
	    } catch (\InvalidArgumentException $e) {
	        return false;
	    }
        return true;
    }

    static public function isValidAuth(string $auth): bool {
		if (!preg_match('/^[A-Za-z0-9_-]{22}=*$/', $auth)) {
		    return false;
	    }
	    try {
	        $a = Base64Url::decode($auth);
	    } catch (\InvalidArgumentException $e) {
	        return false;
	    }
        return strlen($a) === 16;
    }

    private function getClient(): WebPush {
        if (isset($this->client)) {
            return $this->client;
        }
        $this->client = new WebPush();
        return $this->client;
    }

    /**
     * Send one notification - blocking (should be avoided most of the time)
     */
    public function notify(string $endpoint, string $uaPublicKey, string $auth, string $body): void {
        $c = $this->getClient();
        $c->queueNotification(
            new Subscription($endpoint, $uaPublicKey, $auth, "aes128gcm"),
            $body
        );
        // the callback could be defined by the caller
        // For the moment, it is used during registration only - no need to catch 404 &co
        // as the registration isn't activated
        $callback = function($r) {};
        $c->flushPooled($callback);
    }

    /**
     * Send one notification - blocking (should be avoided most of the time)
     */
    public function enqueue(string $endpoint, string $uaPublicKey, string $auth, string $body): void {
        $c = $this->getClient();
        $c->queueNotification(
            new Subscription($endpoint, $uaPublicKey, $auth, "aes128gcm"),
            $body
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
