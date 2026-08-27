# ---------- Stage 1: Composer dependencies ----------
FROM php:8.3-cli AS deps
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libjpeg-dev libicu-dev libonig-dev unzip curl \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd intl mbstring exif pcntl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
# Lock files first so dependency cache invalidates only on real changes
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts --no-interaction --no-progress
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ---------- Stage 2: Frontend assets (Vite) ----------
FROM node:20-alpine AS assets
WORKDIR /app
COPY --from=deps /app .
RUN npm ci && npm run build

# ---------- Stage 3: Runtime ----------
FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libjpeg-dev libicu-dev libonig-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd intl mbstring exif pcntl \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY --from=assets /app .
# Laravel needs writable storage + cache at runtime
RUN chmod -R 777 storage bootstrap/cache
# Deterministic, self-diagnosing boot script
COPY scripts/start.sh /app/start.sh
RUN chmod +x /app/start.sh
EXPOSE 8080
CMD ["sh", "/app/start.sh"]