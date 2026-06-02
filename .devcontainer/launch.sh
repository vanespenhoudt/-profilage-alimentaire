#!/bin/bash
# Lance Laravel et Vite en parallèle

echo "Démarrage Laravel + Vite..."

# Vite en arrière-plan
npm run dev &
VITE_PID=$!

# Laravel au premier plan
php artisan serve --host=0.0.0.0 --port=8000

# Cleanup : arrêter Vite quand Laravel s'arrête
kill $VITE_PID 2>/dev/null
