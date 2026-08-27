#!/bin/sh
set -u

echo "[boot] APP_ENV=${APP_ENV:-<unset>} DB_HOST=${DB_HOST:-<unset>} DB_PORT=${DB_PORT:-<unset>} DB_DATABASE=${DB_DATABASE:-<unset>} DB_USERNAME=${DB_USERNAME:-<unset>} PORT=${PORT:-<unset>}"

# Laravel hard-requires APP_KEY for cookie encryption. If the deploy did not
# set it, generate an ephemeral one so the app responds instead of 500-ing
# on every page. Prefer setting APP_KEY in Railway for persistent sessions.
if [ -z "${APP_KEY:-}" ]; then
    APP_KEY=$(php artisan key:generate --show --no-ansi | tr -d '\r\n ')
    export APP_KEY
    echo "[boot] APP_KEY unset - generated ephemeral key (recommend setting APP_KEY in Railway)"
fi

# Server starts NOW so Railway sees the app responding instantly.
# Migrations and storage housekeeping run in the background.
(
    i=0
    while [ "$i" -lt 12 ]; do
        if php -r 'try { new PDO("mysql:host=".(getenv("DB_HOST") ?: "127.0.0.1").";port=".(getenv("DB_PORT") ?: "3306").";dbname=".(getenv("DB_DATABASE") ?: "soukelkom"), getenv("DB_USERNAME") ?: "root", (string) getenv("DB_PASSWORD")); exit(0); } catch (Throwable $e) { exit(1); }' >/dev/null 2>&1; then
            echo "[boot] database reachable"
            break
        fi
        i=$((i + 1))
        echo "[boot] waiting for database (${i}/12) - server already up"
        sleep 5
    done

    if php artisan migrate --force --quiet; then
        echo "[boot] migrate OK"
    else
        echo "[boot] MIGRATE FAILED - check DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD"
    fi

    php artisan storage:link >/dev/null 2>&1 || echo "[boot] storage already linked"

    if php artisan route:list --quiet >/dev/null 2>&1; then
        echo "[boot] app routes load OK"
    else
        echo "[boot] CODE PROBLEM: routes fail to load"
    fi
) &

echo "[boot] starting server on 0.0.0.0:${PORT:-8080}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"