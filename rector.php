<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/appinfo',
		__DIR__ . '/lib',
		__DIR__ . '/templates',
		__DIR__ . '/tests/Integration',
		__DIR__ . '/tests/Unit',
	])
	->withSkipPath(__DIR__ . '/lib/autoload')
	->withSkipPath(__DIR__ . '/lib/Vendor')
	->withSkipPath(__DIR__ . '/tests/Integration/vendor')
	->withPhpSets(php82: true)
	->withSets([
		PHPUnitSetList::PHPUNIT_120,
		NextcloudSets::NEXTCLOUD_35,
	])
	->withTypeCoverageLevel(0);
