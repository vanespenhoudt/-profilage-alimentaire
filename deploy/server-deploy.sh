#!/bin/bash
# ===========================================================
# DÉPLOIEMENT MANUEL — à exécuter via SSH pour forcer un deploy
# ===========================================================
# ssh c9nzzvn7q_ssh@ssh.c9nzzvn7q.service.one
# bash laravel_app/deploy/server-deploy.sh
# ===========================================================

set -e
cd /customers/b/8/f/c9nzzvn7q/webroots/a44183bb/laravel_app

git pull origin main
php8.2 /usr/local/bin/composer install --no-dev --optimize-autoloader --quiet
php8.2 artisan migrate --force
php8.2 artisan config:cache
php8.2 artisan route:cache
php8.2 artisan view:cache

echo "✓ Deploy terminé $(date)"
