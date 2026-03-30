FROM php:8.4-cli

# Extensions PHP
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libpq-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath gd opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# OPcache pour la performance
RUN echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=10000\nopcache.validate_timestamps=0" > /usr/local/etc/php/conf.d/opcache.ini

# PHP tuning
RUN echo "memory_limit=256M\npost_max_size=20M\nupload_max_filesize=10M" > /usr/local/etc/php/conf.d/custom.ini

# Installer FrankenPHP (serveur production rapide)
# Alternative: on utilise le built-in server avec workers
# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Dépendances PHP
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copier le projet
COPY . .

# Post-install
RUN composer run-script post-autoload-dump 2>/dev/null || true

# Dossiers
RUN mkdir -p storage/framework/{sessions,views,cache/data} \
    && mkdir -p storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Cache config/routes/views (GROS gain de perf)
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

EXPOSE 8000

# Script de démarrage
COPY docker-start.sh /app/docker-start.sh
RUN chmod +x /app/docker-start.sh

CMD ["/app/docker-start.sh"]