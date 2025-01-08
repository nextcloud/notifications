<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Notifications;

use OCP\IUser;

class FakeUser implements IUser {
	public function __construct(
		protected string $userId,
	) {
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

	public function getLastLogin(): int {
		throw new \RuntimeException('Not implemented');
	}

	public function getFirstLogin(): int {
		throw new \RuntimeException('Not implemented');
	}

	public function updateLastLoginTimestamp(): bool {
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

	public function getManagerUids(): array {
		throw new \RuntimeException('Not implemented');
	}

	public function setManagerUids(array $uids): void {
		throw new \RuntimeException('Not implemented');
	}

	public function getPasswordHash(): ?string {
		throw new \RuntimeException('Not implemented');
	}

	public function setPasswordHash(string $passwordHash): bool {
		throw new \RuntimeException('Not implemented');
	}
}
