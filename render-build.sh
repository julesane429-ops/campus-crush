#!/usr/bin/env bash
# Script de build pour Render.com
# Exécuté automatiquement à chaque déploiement

echo "🚀 Campus Crush - Build en cours..."

# Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# Générer la clé si elle n'existe pas
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# NE PAS cacher la config ici : les variables d'environnement Render
# (OPENAI_API_KEY, etc.) ne sont disponibles qu'au runtime, pas au build.
# Le config:cache est fait dans la startCommand (render.yaml).
php artisan view:cache

# Lancer les migrations
php artisan migrate --force --seed

# Créer le lien symbolique pour le storage
php artisan storage:link 2>/dev/null || true

echo "✅ Build terminé !"