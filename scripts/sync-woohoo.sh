#!/usr/bin/env bash
set -e
cd "$(dirname "$0")/.."
php artisan giftbox:sync-all "$@"
