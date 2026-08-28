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
# Generate a production .env INSIDE the image with a real APP_KEY.
# Railway environment variables still override these values at runtime.
RUN printf 'APP_NAME=Soukelkom\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost\nLOG_CHANNEL=stack\nLOG_LEVEL=error\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=soukelkom\nDB_USERNAME=root\nDB_PASSWORD=\nSESSION_DRIVER=database\nCACHE_STORE=database\nQUEUE_CONNECTION=database\nFILESYSTEM_DISK=local\nSCOUT_DRIVER=collection\nMAIL_MAILER=log\n' > /app/.env \
    && php artisan key:generate --force --no-ansi \
    && grep -c 'APP_KEY=' /app/.env
# Deterministic, self-diagnosing boot script
COPY scripts/start.sh /app/start.sh
RUN chmod +x /app/start.sh
EXPOSE 8080
CMD ["sh", "/app/start.sh"]