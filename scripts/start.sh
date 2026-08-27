#!/bin/sh
set -u

echo "[boot] APP_ENV=${APP_ENV:-<unset>} DB_HOST=${DB_HOST:-<unset>} DB_PORT=${DB_PORT:-<unset>} DB_DATABASE=${DB_DATABASE:-<unset>} PORT=${PORT:-<unset>}"

# Give the database up to 60s to become reachable before migrating
i=0
while [ "$i" -lt 12 ]; do
    if php -r 'try { new PDO("mysql:host=".(getenv("DB_HOST") ?: "127.0.0.1").";port=".(getenv("DB_PORT") ?: "3306").";dbname=".(getenv("DB_DATABASE") ?: "soukelkom"), getenv("DB_USERNAME") ?: "root", (string) getenv("DB_PASSWORD")); exit(0); } catch (Throwable $e) { exit(1); }' >/dev/null 2>&1; then
        echo "[boot] database reachable"
        break
    fi
    i=$((i + 1))
    echo "[boot] waiting for database (${i}/12)..."
    sleep 5
done

php artisan migrate --force --quiet
echo "[boot] migrate exit=$?"

php artisan storage:link >/dev/null 2>&1 || echo "[boot] storage already linked"

if php artisan route:list --quiet >/dev/null 2>&1; then
    echo "[boot] app boots OK (routes load)"
else
    echo "[boot] ERROR: routes do not load - check app configuration"
fi

echo "[boot] starting server on 0.0.0.0:${PORT:-8080}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"