<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

use OCA\Notifications\AppInfo\Application;
use OCA\Notifications\Vendor\Base64Url\Base64Url;
use OCA\Notifications\Vendor\Minishlink\WebPush\Subscription;
use OCA\Notifications\Vendor\Minishlink\WebPush\Utils;
use OCA\Notifications\Vendor\Minishlink\WebPush\VAPID;
use OCA\Notifications\Vendor\Minishlink\WebPush\WebPush;
use OCP\IAppConfig;

class WebPushClient {
    private WebPush $client;
    /** @psalm-var array{publicKey: string, privateKey: string} */
    private array $vapid;

    public function __construct(
        protected IAppConfig $appConfig,
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
        $this->client = new WebPush(auth: $this->getVapid());
        $this->client->setReuseVAPIDHeaders(true);
        return $this->client;
    }

    /**
     * @return array
     * @psalm-return array{publicKey: string, privateKey: string}
     */
    private function getVapid(): array {
        if (isset($this->vapid) && array_key_exists('publicKey', $this->vapid) && array_key_exists('privateKey', $this->vapid)) {
            return $this->vapid;
        }
        $publicKey = $this->appConfig->getValueString(
            Application::APP_ID,
            'webpush_vapid_pubkey',
            lazy: true
        );
        $privateKey = $this->appConfig->getValueString(
            Application::APP_ID,
            'webpush_vapid_privkey',
            lazy: true
        );
        if ($publicKey === '' || $privateKey === '') {
            $this->vapid = VAPID::createVapidKeys();
            $this->appConfig->setValueString(
                Application::APP_ID,
                'webpush_vapid_pubkey',
                $this->vapid['publicKey'],
                lazy: true,
                sensitive: true
            );
            $this->appConfig->setValueString(
                Application::APP_ID,
                'webpush_vapid_privkey',
                $this->vapid['privateKey'],
                lazy: true,
                sensitive: true
            );
        } else {
            $this->vapid = [
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ];
        }
        return $this->vapid;
    }

    /**
     * @return string
     */
    public function getVapidPublicKey(): string {
        return $this->getVapid()['publicKey'];
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
     * @throws ErrorException
     */
    public function enqueue(string $endpoint, string $uaPublicKey, string $auth, string $body, string $urgency = 'normal'): void {
        $c = $this->getClient();
        $c->queueNotification(
            new Subscription($endpoint, $uaPublicKey, $auth, "aes128gcm"),
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
