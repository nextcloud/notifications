#!/usr/bin/env bash

APP_NAME=notifications

APP_INTEGRATION_DIR=$PWD
ROOT_DIR=${APP_INTEGRATION_DIR}/../../../..

cd ${ROOT_DIR}/build/integration
composer install
cd ${APP_INTEGRATION_DIR}

php -S localhost:8080 -t ${ROOT_DIR} &
PHPPID=$!
echo $PHPPID

${ROOT_DIR}/occ config:app:set ${APP_NAME} debug --value="on"

export TEST_SERVER_URL="http://localhost:8080/ocs/"
${ROOT_DIR}/build/integration/vendor/bin/behat -f junit -f pretty
RESULT=$?

kill $PHPPID

${ROOT_DIR}/occ config:app:delete ${APP_NAME} debug

exit $RESULT
