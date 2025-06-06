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

	#[\Override]
	public function getUID(): string {
		return $this->userId;
	}

	#[\Override]
	public function getCloudId() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getSystemEMailAddress(): ?string {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getPrimaryEMailAddress(): ?string {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getDisplayName() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setDisplayName($displayName) {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getLastLogin(): int {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getFirstLogin(): int {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function updateLastLoginTimestamp(): bool {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function delete() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setPassword($password, $recoveryPassword = null) {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getHome() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getBackendClassName() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getBackend(): ?\OCP\UserInterface {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function canChangeAvatar() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function canChangePassword() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function canChangeDisplayName() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function canChangeEmail(): bool {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function isEnabled() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setEnabled(bool $enabled = true) {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getEMailAddress() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getAvatarImage($size) {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setEMailAddress($mailAddress) {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getQuota() {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getQuotaBytes(): int|float {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setQuota($quota) {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setSystemEMailAddress(string $mailAddress): void {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setPrimaryEMailAddress(string $mailAddress): void {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getManagerUids(): array {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setManagerUids(array $uids): void {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function getPasswordHash(): ?string {
		throw new \RuntimeException('Not implemented');
	}

	#[\Override]
	public function setPasswordHash(string $passwordHash): bool {
		throw new \RuntimeException('Not implemented');
	}
}
