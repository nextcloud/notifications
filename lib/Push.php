<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
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


use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\Notification\IManager;
use OCP\Notification\INotification;

class Push {
	/** @var IDBConnection */
	protected $connection;
	/** @var IManager */
	protected $manager;
	/** @var IConfig */
	protected $config;
	/** @var IProvider */
	protected $tokenProvider;
	/** @var IClientService */
	protected $clientService;
	/** @var ILogger */
	protected $log;

	public function __construct(IDBConnection $connection, IManager $manager, IConfig $config, IProvider $tokenProvider, IClientService $clientService, ILogger $log) {
		$this->connection = $connection;
		$this->manager = $manager;
		$this->config = $config;
		$this->tokenProvider = $tokenProvider;
		$this->clientService = $clientService;
		$this->log = $log;
	}

	/**
	 * @param INotification $notification
	 */
	public function pushToDevice(INotification $notification) {
		$devices = $this->getDevicesForUser($notification->getUser());

		if (empty($devices)) {
			return;
		}

		$language = $this->config->getUserValue($notification->getUser(), 'core', 'lang', 'en');
		try {
			$notification = $this->manager->prepare($notification, $language);
		} catch (\InvalidArgumentException $e) {
			return;
		}

		$subject = $notification->getParsedSubject();

		$collection = [];
		foreach ($devices as $device) {
			try {
				$collection[] = $this->encryptAndSign($device, $subject);
			} catch (InvalidTokenException $e) {
				// Token does not exist anymore, should drop the push device entry
				// FIXME delete push token
			}
		}

		$payload = json_encode($collection);

		$this->log->alert($payload); // TODO TEMP

		$client = $this->clientService->newClient();
		try {
			$response = $client->post('http://127.0.0.1:3306', [
				'body' => $payload,
			]);
		} catch (\Exception $e) {
			$this->log->logException($e, [
				'app' => 'notifications',
			]);
			return;
		}
	}

	/**
	 * @param array $device
	 * @param $subject
	 * @return array
	 * @throws InvalidTokenException
	 */
	protected function encryptAndSign(array $device, $subject) {
		// Check if the token is still valid...
		$this->tokenProvider->getTokenById($device['token']);

		$encryptedSubject = json_encode($subject); // FIXME use $device['devicepublickey']
		$signature = hash('sha512', $encryptedSubject); // FIXME use $userPrivateKey
		return [
			'pushTokenHash' => $device['pushtokenhash'],
			'subject' => $encryptedSubject,
			'signature' => $signature,
		];
	}

	/**
	 * @param string $uid
	 * @return array[]
	 */
	protected function getDevicesForUser($uid) {
		$query = $this->connection->getQueryBuilder();
		$query->select('*')
			->from('notifications_pushtokens')
			->where($query->expr()->eq('uid', $uid));

		$result = $query->execute();
		$devices = $result->fetchAll();
		$result->closeCursor();

		return $devices;
	}
}
