#!/usr/bin/env sh
echo "Installing dependencies...\n"
composer install --dev

echo "Running tests...\n"
./vendor/bin/phpunit tests/

echo "Compiling SCSS to CSS"
php ./compile_templates.php
