#!/usr/bin/env sh
set -eu

cd /var/www/html

is_true() {
    case "${1:-}" in
        1|true|TRUE|yes|YES|on|ON) return 0 ;;
        *) return 1 ;;
    esac
}

app_host_from_url() {
    value="${1#http://}"
    value="${value#https://}"
    value="${value%%/*}"
    value="${value%%:*}"
    printf '%s' "${value}"
}

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    /etc/apache2/ssl

if is_true "${FIX_PERMISSIONS:-true}"; then
    chown -R www-data:www-data bootstrap/cache storage 2>/dev/null || true
fi

if [ "$#" -gt 0 ] && [ "$1" != "start" ]; then
    exec "$@"
fi

TLS_COMMON_NAME="${TLS_COMMON_NAME:-${SERVER_NAME:-}}"

if [ -z "${TLS_COMMON_NAME}" ] && [ -n "${APP_URL:-}" ]; then
    TLS_COMMON_NAME="$(app_host_from_url "${APP_URL}")"
fi

if [ -z "${TLS_COMMON_NAME}" ]; then
    TLS_COMMON_NAME="localhost"
fi

export APACHE_SERVER_NAME="${SERVER_NAME:-${TLS_COMMON_NAME}}"
printf 'ServerName %s\n' "${APACHE_SERVER_NAME}" > /etc/apache2/conf-available/docker-server-name.conf
a2enconf docker-server-name >/dev/null

if [ ! -f /etc/apache2/ssl/server.crt ] || [ ! -f /etc/apache2/ssl/server.key ]; then
    san="DNS:${TLS_COMMON_NAME}"

    if printf '%s' "${TLS_COMMON_NAME}" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$'; then
        san="IP:${TLS_COMMON_NAME}"
    fi

    cat > /tmp/self-signed-openssl.cnf <<EOF
[req]
distinguished_name = req_distinguished_name
x509_extensions = v3_req
prompt = no

[req_distinguished_name]
CN = ${TLS_COMMON_NAME}

[v3_req]
subjectAltName = ${san}
EOF

    openssl req \
        -x509 \
        -nodes \
        -newkey rsa:4096 \
        -sha256 \
        -days "${TLS_DAYS:-3650}" \
        -keyout /etc/apache2/ssl/server.key \
        -out /etc/apache2/ssl/server.crt \
        -config /tmp/self-signed-openssl.cnf

    chmod 600 /etc/apache2/ssl/server.key
    rm -f /tmp/self-signed-openssl.cnf
fi

if [ -z "${APP_KEY:-}" ] && is_true "${GENERATE_APP_KEY:-true}"; then
    if [ -f .env ]; then
        php artisan key:generate --force
    else
        APP_KEY="$(php artisan key:generate --show)"
        export APP_KEY
        echo "APP_KEY was generated for this container process. Set a persistent APP_KEY for production." >&2
    fi
fi

if is_true "${RUN_MIGRATIONS:-true}"; then
    php artisan migrate --force
fi

if is_true "${RUN_SEEDERS:-true}"; then
    php artisan db:seed --force
fi

if is_true "${RUN_OPTIMIZE:-true}"; then
    php artisan optimize
fi

exec apache2-foreground
