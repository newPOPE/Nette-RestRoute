#!/bin/bash

if [ "$1" = "install" ]; then
  composer install --prefer-dist
  exit $?
fi

if [ "$1" = "tests" ]; then
  vendor/bin/phpunit
  exit $?
fi

exec "$@"
