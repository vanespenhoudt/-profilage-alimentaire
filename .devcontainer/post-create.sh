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

# Forcer les variables de base de données pour Codespaces
# Les patterns couvrent les lignes commentées (# DB_HOST=...) et non commentées
sed -i 's/^[# ]*DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/^[# ]*DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/^[# ]*DB_PORT=.*/DB_PORT=3306/' .env
sed -i 's/^[# ]*DB_DATABASE=.*/DB_DATABASE=profilage/' .env
sed -i 's/^[# ]*DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/^[# ]*DB_PASSWORD=.*/DB_PASSWORD=root/' .env

echo "==> Génération de la clé APP_KEY..."
# Ne génère une clé que si .env n'en a pas déjà une valide
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=base64:$" .env; then
    php artisan key:generate
else
    echo "    APP_KEY déjà présente, on garde l'existante."
fi

echo "==> Attente de MySQL..."
until mysqladmin ping -h 127.0.0.1 -u root -proot --silent 2>/dev/null; do
    sleep 1
done

echo "==> Exécution des migrations..."
php artisan migrate --force

echo "==> Lien storage..."
php artisan storage:link

echo ""
echo "✅ Environnement prêt !"
echo "   Lance le serveur Laravel : php artisan serve"
echo "   Lance Vite               : npm run dev"
echo "   Les deux en même temps   : bash .devcontainer/launch.sh"
