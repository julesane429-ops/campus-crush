#!/usr/bin/env bash
# Script de build pour Render.com
# Exécuté automatiquement à chaque déploiement

echo "🚀 Campus Crush - Build en cours..."

# Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# Compiler les assets Vite utilises par @vite().
# public/build est ignore par Git, donc Render doit le recreer au deploy.
if command -v npm >/dev/null 2>&1; then
    npm install
    npm run build
else
    echo "npm introuvable: assure-toi que public/build est fourni ou que Node est disponible sur Render."
fi

# Générer la clé si elle n'existe pas
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# NE PAS cacher la config ici : les variables d'environnement Render
# (OPENAI_API_KEY, etc.) ne sont disponibles qu'au runtime, pas au build.
# Le config:cache est fait dans la startCommand (render.yaml).
# Supprimer les caches existants pour éviter les problèmes de route 404
php artisan cache:clear
php artisan route:clear
php artisan view:cache

# Lancer les migrations et les seeds structurels uniquement.
# Ne pas appeler DatabaseSeeder en production: il cree des profils fictifs.
php artisan migrate --force
php artisan db:seed --class=UniversitySeeder --force
php artisan db:seed --class=AdminSeeder --force

# Créer le lien symbolique pour le storage
php artisan storage:link 2>/dev/null || true

echo "✅ Build terminé !"
