#!/usr/bin/env bash

# SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
# SPDX-FileCopyrightText: 2015 ownCloud, Inc.
# SPDX-License-Identifier: AGPL-3.0-or-later

APP_NAME=notifications

APP_INTEGRATION_DIR=$PWD
ROOT_DIR=${APP_INTEGRATION_DIR}/../../../..
composer install

#php -S localhost:8080 -t ${ROOT_DIR} &
#PHPPID=$!
#echo $PHPPID

cp -R ./app ../../../notificationsintegrationtesting
${ROOT_DIR}/occ app:enable notifications
${ROOT_DIR}/occ app:enable --force notificationsintegrationtesting
${ROOT_DIR}/occ app:enable provisioning_api
${ROOT_DIR}/occ app:list | grep notifications
${ROOT_DIR}/occ app:list | grep provisioning_api

export TEST_SERVER_URL="http://localhost:8080/"
${APP_INTEGRATION_DIR}/vendor/bin/behat --colors -f junit -f pretty $1 $2
RESULT=$?

#kill $PHPPID

${ROOT_DIR}/occ app:disable notificationsintegrationtesting
rm -rf ../../../notificationsintegrationtesting

exit $RESULT
