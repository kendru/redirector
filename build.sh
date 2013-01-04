#!/usr/bin/env sh
composer install --dev
./vendor/bin/phpunit tests/
