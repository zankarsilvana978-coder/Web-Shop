#!/bin/sh
set -u

echo "[boot] APP_ENV=${APP_ENV:-<unset>} DB_HOST=${DB_HOST:-<unset>} DB_PORT=${DB_PORT:-<unset>} DB_DATABASE=${DB_DATABASE:-<unset>} DB_USERNAME=${DB_USERNAME:-<unset>} PORT=${PORT:-<unset>}"

# Never let a cached config pin old DB/env values
php artisan optimize:clear >/dev/null 2>&1
echo "[boot] config/route/view caches cleared"

# Laravel hard-requires APP_KEY for cookie encryption. If the deploy did not
# set it, generate an ephemeral one so the app responds instead of 500-ing.
if [ -z "${APP_KEY:-}" ]; then
    APP_KEY=$(php artisan key:generate --show --no-ansi | tr -d '\r\n ')
    export APP_KEY
    echo "[boot] APP_KEY unset - generated ephemeral key (recommend setting APP_KEY in Railway)"
fi

# Wait for the database to be reachable (honors DATABASE_URL or DB_* vars)
i=0
while [ "$i" -lt 8 ]; do
    if php -r '
        $url = getenv("DATABASE_URL") ?: getenv("DB_URL");
        if ($url) {
            $p = parse_url($url);
            $dsn = "mysql:host=".($p["host"] ?? "127.0.0.1").";port=".($p["port"] ?? "3306").";dbname=".ltrim($p["path"] ?? "/", "/");
            $user = $p["user"] ?? "root";
            $pass = $p["pass"] ?? "";
        } else {
            $dsn = "mysql:host=".(getenv("DB_HOST") ?: "127.0.0.1").";port=".(getenv("DB_PORT") ?: "3306").";dbname=".(getenv("DB_DATABASE") ?: "soukelkom");
            $user = getenv("DB_USERNAME") ?: "root";
            $pass = (string) getenv("DB_PASSWORD");
        }
        try { new PDO($dsn, $user, $pass); exit(0); } catch (Throwable $e) { exit(1); }
    ' >/dev/null 2>&1; then
        echo "[boot] database reachable"
        break
    fi
    i=$((i + 1))
    echo "[boot] waiting for database (${i}/8)..."
    sleep 5
done

# Migrate in the foreground (deterministic, retried, errors visible)
attempt=1
while [ "$attempt" -le 3 ]; do
    if php artisan migrate --force --no-interaction > /tmp/migrate.log 2>&1; then
        echo "[boot] migrate OK"
        break
    fi
    echo "[boot] migrate attempt $attempt failed:"
    tail -n 4 /tmp/migrate.log
    attempt=$((attempt + 1))
    sleep 4
done

# Seed demo content (idempotent - safe on every boot)
if php artisan db:seed --force --no-interaction > /tmp/seed.log 2>&1; then
    echo "[boot] demo content seeded OK"
else
    echo "[boot] seed failed:"
    tail -n 4 /tmp/seed.log
fi

php artisan storage:link >/dev/null 2>&1 || echo "[boot] storage already linked"

echo "[boot] starting server on 0.0.0.0:${PORT:-8080}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"