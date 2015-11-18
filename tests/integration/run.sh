#!/usr/bin/env bash

composer install

php -S localhost:8080 -t ../../../.. &
PHPPID=$!
echo $PHPPID

export TEST_SERVER_URL="http://localhost:8080/ocs/"
vendor/bin/behat -f junit -f pretty
RESULT=$?

kill $PHPPID

exit $RESULT
