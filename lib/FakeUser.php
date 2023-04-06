<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022, Joas Schilling <coding@schilljs.com>
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

namespace OCA\Notifications;

use OCP\IUser;

class FakeUser implements IUser {
	protected string $userId;

	public function __construct(string $userId) {
		$this->userId = $userId;
	}

	public function getUID(): string {
		return $this->userId;
	}

	public function getCloudId() {
		throw new \RuntimeException('Not implemented');
	}

	public function getSystemEMailAddress(): ?string {
		throw new \RuntimeException('Not implemented');
	}

	public function getPrimaryEMailAddress(): ?string {
		throw new \RuntimeException('Not implemented');
	}

	public function getDisplayName() {
		throw new \RuntimeException('Not implemented');
	}

	public function setDisplayName($displayName) {
		throw new \RuntimeException('Not implemented');
	}

	public function getLastLogin() {
		throw new \RuntimeException('Not implemented');
	}

	public function updateLastLoginTimestamp() {
		throw new \RuntimeException('Not implemented');
	}

	public function delete() {
		throw new \RuntimeException('Not implemented');
	}

	public function setPassword($password, $recoveryPassword = null) {
		throw new \RuntimeException('Not implemented');
	}

	public function getHome() {
		throw new \RuntimeException('Not implemented');
	}

	public function getBackendClassName() {
		throw new \RuntimeException('Not implemented');
	}

	public function getBackend(): ?\OCP\UserInterface {
		throw new \RuntimeException('Not implemented');
	}

	public function canChangeAvatar() {
		throw new \RuntimeException('Not implemented');
	}

	public function canChangePassword() {
		throw new \RuntimeException('Not implemented');
	}

	public function canChangeDisplayName() {
		throw new \RuntimeException('Not implemented');
	}

	public function isEnabled() {
		throw new \RuntimeException('Not implemented');
	}

	public function setEnabled(bool $enabled = true) {
		throw new \RuntimeException('Not implemented');
	}

	public function getEMailAddress() {
		throw new \RuntimeException('Not implemented');
	}

	public function getAvatarImage($size) {
		throw new \RuntimeException('Not implemented');
	}

	public function setEMailAddress($mailAddress) {
		throw new \RuntimeException('Not implemented');
	}

	public function getQuota() {
		throw new \RuntimeException('Not implemented');
	}

	public function setQuota($quota) {
		throw new \RuntimeException('Not implemented');
	}

	public function setSystemEMailAddress(string $mailAddress): void {
		throw new \RuntimeException('Not implemented');
	}

	public function setPrimaryEMailAddress(string $mailAddress): void {
		throw new \RuntimeException('Not implemented');
	}
}
