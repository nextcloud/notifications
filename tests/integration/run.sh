#!/usr/bin/env bash

APP_NAME=notifications

APP_INTEGRATION_DIR=$PWD
ROOT_DIR=${APP_INTEGRATION_DIR}/../../../..

#cd ${ROOT_DIR}/build/integration
composer install
#cd ${APP_INTEGRATION_DIR}

php -S localhost:8080 -t ${ROOT_DIR} &
PHPPID=$!
echo $PHPPID

cp -R ./app ../../../notificationsintegrationtesting
${ROOT_DIR}/occ app:enable notifications
${ROOT_DIR}/occ app:enable notificationsintegrationtesting

export TEST_SERVER_URL="http://localhost:8080/"
${APP_INTEGRATION_DIR}/vendor/bin/behat -f junit -f pretty
RESULT=$?

kill $PHPPID

${ROOT_DIR}/occ app:disable notificationsintegrationtesting
rm -rf ../../../notificationsintegrationtesting

exit $RESULT
