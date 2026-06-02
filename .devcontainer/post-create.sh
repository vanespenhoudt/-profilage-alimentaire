#!/bin/bash
set -e

echo "==> Installation des dépendances PHP..."
composer install --no-interaction --prefer-dist

echo "==> Installation des dépendances Node..."
npm install

echo "==> Configuration du fichier .env..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

# SQLite pour Codespaces
sed -i 's/^[# ]*DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
touch database/database.sqlite
sed -i 's/^[# ]*DB_DATABASE=.*/DB_DATABASE=\/workspaces\/-profilage-alimentaire\/database\/database.sqlite/' .env

echo "==> Génération de la clé APP_KEY..."
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=base64:$" .env; then
    php artisan key:generate
else
    echo "    APP_KEY déjà présente, on garde l'existante."
fi

echo "==> Exécution des migrations..."
php artisan migrate --force

echo "==> Lien storage..."
php artisan storage:link

echo ""
echo "✅ Environnement prêt !"
echo "   Lance le serveur Laravel : php artisan serve"
echo "   Lance Vite               : npm run dev"
echo "   Les deux en même temps   : bash .devcontainer/launch.sh"
