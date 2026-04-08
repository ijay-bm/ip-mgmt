#!/bin/sh
set -e

envsubst < .env.local.template > .env

composer install --no-interaction --prefer-dist --no-progress

php artisan key:generate --force --ansi
php artisan migrate:fresh --seed --force --ansi

php artisan config:clear --ansi
php artisan route:clear --ansi

exec "$@"