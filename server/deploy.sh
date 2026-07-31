#!/bin/bash
# Deploys the latest master branch: pull, install, build, migrate, cache.
# Every step runs as www-data so the weekly permission normalisation
# (www-data:www-data ownership) and deploys never fight each other.
set -euo pipefail
cd /var/www/juneun
run() { sudo -u www-data -H env HOME=/var/www COMPOSER_HOME=/var/www/.composer npm_config_cache=/var/www/.npm "$@"; }
run php8.4 artisan down || true
run git pull --ff-only
run composer install --no-dev --optimize-autoloader --no-interaction
run npm ci --silent && run npm run build
run php8.4 artisan migrate --force
run php8.4 artisan optimize
sudo systemctl reload php8.4-fpm
run php8.4 artisan up
echo "deployed: $(git log --oneline -1)"
