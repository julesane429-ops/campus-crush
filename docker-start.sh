#!/bin/bash
set -e

# Migrations (une seule fois, rapide si rien à faire)
php artisan migrate --force 2>/dev/null || true

# Seeds seulement si nécessaire (vérifie si admin existe)
php artisan db:seed --class=AdminSeeder --force 2>/dev/null || true
php artisan db:seed --class=UniversitySeeder --force 2>/dev/null || true

# Storage link
php artisan storage:link 2>/dev/null || true

# Re-cache après migrate (les .env sont dispo au runtime)
php artisan config:cache
php artisan route:cache

# Démarrer avec PHP built-in MAIS avec plus de workers
# Le trick: lancer plusieurs processus PHP
echo "Starting Campus Crush on port ${PORT:-8000}..."

# Option 1: PHP built-in (simple mais mono-thread)
# php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

# Option 2: PHP built-in avec PHP_CLI_SERVER_WORKERS (PHP 8.4+)
PHP_CLI_SERVER_WORKERS=4 php -S 0.0.0.0:${PORT:-8000} -t public/