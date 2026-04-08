#!/bin/sh
set -e

npm i

envsubst < .env.local.template > .env

exec "$@"