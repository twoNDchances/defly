#!/bin/sh
set -eu

SECRET_KEY_PATH="${SECRET_KEY_FILE:-storage/secret/key.txt}"
TLS_CERT_PATH="${ORCHESTRATOR_TLS_CERT_FILE:-storage/tls/orchestrator.crt}"
TLS_KEY_PATH="${ORCHESTRATOR_TLS_KEY_FILE:-storage/tls/orchestrator.key}"

mkdir -p "$(dirname "$SECRET_KEY_PATH")" "$(dirname "$TLS_CERT_PATH")" "$(dirname "$TLS_KEY_PATH")"

if [ ! -f "$SECRET_KEY_PATH" ]; then
    python manage.py generatesecretkeyfile
fi

if [ ! -f "$TLS_CERT_PATH" ] || [ ! -f "$TLS_KEY_PATH" ]; then
    python manage.py generatetlsfile
fi

exec "$@"
