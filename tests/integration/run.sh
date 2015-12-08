#!/usr/bin/env bash

APP_INTEGRATION_DIR=$PWD
ROOT_DIR=../../../..

cd ${ROOT_DIR}/build/integration
composer install
cd ${APP_INTEGRATION_DIR}

php -S localhost:8080 -t ${ROOT_DIR} &
PHPPID=$!
echo $PHPPID

export TEST_SERVER_URL="http://localhost:8080/ocs/"
${ROOT_DIR}/build/integration/vendor/bin/behat -f junit -f pretty
RESULT=$?

kill $PHPPID

exit $RESULT
