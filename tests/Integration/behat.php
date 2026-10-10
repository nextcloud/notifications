<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2015 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Behat\Config\Config;
use Behat\Config\Formatter\JUnitFormatter;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
	->withProfile((new Profile('default', [
		'autoload' => [
			'' => '%paths.base%/features/bootstrap',
		],
	]))
		->withFormatter((new JUnitFormatter())
			->withOutputPath('%paths.base%/output'))
		->withFormatter((new PrettyFormatter())
			->withOutputStyles([
				'comment' => [
					'bright-blue',
				],
			]))
		->withSuite((new Suite('default'))
			->addContext(FeatureContext::class)
			->withPaths('%paths.base%/features')));
