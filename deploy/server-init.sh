#!/bin/bash
# ===========================================================
# INITIALISATION ONE.COM — à exécuter UNE SEULE FOIS via SSH
# ===========================================================
# ssh c9nzzvn7q_ssh@ssh.c9nzzvn7q.service.one
# bash laravel_app/deploy/server-init.sh
# ===========================================================

set -e

APP_DIR="/customers/b/8/f/c9nzzvn7q/webroots/a44183bb/laravel_app"
WEB_ROOT="/customers/b/8/f/c9nzzvn7q/webroots/a44183bb/httpdocs"

echo "── 1. Composer install ──────────────────────────────────"
cd "$APP_DIR"
php8.2 /usr/local/bin/composer install --no-dev --optimize-autoloader

echo "── 2. Migrations ────────────────────────────────────────"
php8.2 artisan migrate --force

echo "── 3. Permissions storage ───────────────────────────────"
chmod -R 775 storage bootstrap/cache

echo "── 4. Symlink public/ → httpdocs/profilage/ ─────────────"
rm -rf "$WEB_ROOT/profilage"
ln -s "$APP_DIR/public" "$WEB_ROOT/profilage"

echo "── 5. Geler les fichiers de config serveur ──────────────"
# Ces fichiers ont une version spécifique au serveur (RewriteBase, chemins absolus).
# skip-worktree = git pull ne les écrasera JAMAIS.
git update-index --skip-worktree public/.htaccess
git update-index --skip-worktree public/index.php
echo "   → public/.htaccess   [skip-worktree ✓]"
echo "   → public/index.php   [skip-worktree ✓]"

echo "── 6. Cache de production ───────────────────────────────"
php8.2 artisan config:cache
php8.2 artisan route:cache
php8.2 artisan view:cache

echo ""
echo "✓ Initialisation terminée."
echo "  Vérifie : https://mve-nutrition.be/profilage"
