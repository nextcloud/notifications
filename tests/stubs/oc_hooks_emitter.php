<?php

/**
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OC\Hooks {
	interface Emitter {
		public function listen($scope, $method, callable $callback);

		public function removeListener($scope = null, $method = null, ?callable $callback = null);
	}
}
