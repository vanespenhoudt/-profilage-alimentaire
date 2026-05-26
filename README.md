# Profilage Alimentaire

Application web de profilage nutritionnel pour conseillers en alimentation.

**Stack :** Laravel 11 · PHP 8.2 · Bootstrap 5 · Vite

## Modules

- Gestion des clients (conseillers / super admin)
- Questionnaire nutritionnel complet (5 profils : Typage Métabolique, Ayurveda, Julia Ross, Diathèse de Ménétrier, Bilan Hormonal)
- Scoring automatique et page bilan visuelle

## Installation

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
```
