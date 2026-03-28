FROM php:8.2-cli

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Répertoire de travail
WORKDIR /app

# Copier les fichiers composer et installer les dépendances
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copier tout le projet
COPY . .

# Post-install scripts
RUN composer run-script post-autoload-dump 2>/dev/null || true

# Créer les dossiers nécessaires
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Optimiser pour la production
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port (Render injecte $PORT)
EXPOSE 8000

# Commande de démarrage
CMD php artisan migrate --force 2>/dev/null; \
    php artisan storage:link 2>/dev/null || true; \
    php artisan config:cache; \
    php artisan route:cache; \
    php artisan view:cache; \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
