#!/bin/bash -ex

ls -alh
pwd
env

echo "--- Composer install"
composer install

echo "+++ test"
./vendor/bin/phpunit

# If this is a new tag build, push to gemfury to package.
if git describe --tags --exact-match &>/dev/null ; then
    echo "~~~ update gemfury"
    git push --tags https://${GEMFURY_CREDENTIALS}@git.fury.io/99designs/ergo-http.git master
fi
