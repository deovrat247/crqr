#!/usr/bin/env bash
# Deploy the latest main branch. Run on the server as the site user:
#   cd ~/htdocs/crqr.in && bash deploy.sh
set -euo pipefail

PHP="${PHP:-php8.5}"
COMPOSER="$(command -v composer)"

cd "$(dirname "$0")"

git pull --ff-only

"$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction

"$PHP" artisan down --retry=5 || true
trap '"$PHP" artisan up' EXIT

"$PHP" artisan migrate --force
"$PHP" artisan storage:link 2>/dev/null || true
"$PHP" artisan optimize:clear
"$PHP" artisan optimize

echo "Deployed $(git rev-parse --short HEAD)"
