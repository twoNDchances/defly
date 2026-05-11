#!/usr/bin/env sh
set -eu

cd /app

is_true() {
    case "${1:-}" in
        1|true|TRUE|yes|YES|on|ON) return 0 ;;
        *) return 1 ;;
    esac
}

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs

if is_true "${FIX_PERMISSIONS:-true}"; then
    chown -R www-data:www-data bootstrap/cache storage 2>/dev/null || true
fi

if [ "$#" -gt 0 ] && [ "$1" != "start" ]; then
    exec "$@"
fi

if is_true "${RUN_MIGRATIONS:-true}"; then
    php artisan migrate --force
fi

if is_true "${RUN_SEEDERS:-true}"; then
    php artisan db:seed --force
fi

OCTANE_HTTPS="${OCTANE_HTTPS:-true}"
OCTANE_HOST="${OCTANE_HOST:-}"
OCTANE_PORT="${OCTANE_PORT:-}"
OCTANE_ADMIN_HOST="${OCTANE_ADMIN_HOST:-localhost}"
OCTANE_ADMIN_PORT="${OCTANE_ADMIN_PORT:-2019}"
OCTANE_WORKERS="${OCTANE_WORKERS:-auto}"
OCTANE_MAX_REQUESTS="${OCTANE_MAX_REQUESTS:-500}"
OCTANE_LOG_LEVEL="${OCTANE_LOG_LEVEL:-WARN}"
OCTANE_HTTP_REDIRECT="${OCTANE_HTTP_REDIRECT:-true}"

if [ -z "${OCTANE_HOST}" ] && [ -n "${SERVER_NAME:-}" ]; then
    OCTANE_HOST="${SERVER_NAME}"
fi

if [ -z "${OCTANE_HOST}" ] && [ -n "${APP_URL:-}" ]; then
    OCTANE_HOST="${APP_URL#http://}"
    OCTANE_HOST="${OCTANE_HOST#https://}"
    OCTANE_HOST="${OCTANE_HOST%%/*}"
    OCTANE_HOST="${OCTANE_HOST%%:*}"

    case "${OCTANE_HOST}" in
        localhost|127.0.0.1|0.0.0.0) OCTANE_HOST="" ;;
    esac
fi

if [ -z "${OCTANE_HOST}" ]; then
    if is_true "${OCTANE_HTTPS}"; then
        echo "OCTANE_HTTPS=true requires SERVER_NAME or OCTANE_HOST, for example SERVER_NAME=manager.example.com" >&2
        exit 1
    fi

    OCTANE_HOST="0.0.0.0"
fi

if [ -z "${OCTANE_PORT}" ]; then
    if is_true "${OCTANE_HTTPS}"; then
        OCTANE_PORT=443
    else
        OCTANE_PORT=8000
    fi
fi

set -- php artisan octane:frankenphp \
    "--host=${OCTANE_HOST}" \
    "--port=${OCTANE_PORT}" \
    "--admin-host=${OCTANE_ADMIN_HOST}" \
    "--admin-port=${OCTANE_ADMIN_PORT}" \
    "--workers=${OCTANE_WORKERS}" \
    "--max-requests=${OCTANE_MAX_REQUESTS}" \
    "--log-level=${OCTANE_LOG_LEVEL}"

if [ -n "${OCTANE_CADDYFILE:-}" ]; then
    set -- "$@" "--caddyfile=${OCTANE_CADDYFILE}"
fi

if is_true "${OCTANE_HTTPS}"; then
    set -- "$@" --https

    if is_true "${OCTANE_HTTP_REDIRECT}"; then
        set -- "$@" --http-redirect
    fi
fi

if is_true "${RUN_OPTIMIZE:-true}"; then
    php artisan optimize
fi

exec "$@"
