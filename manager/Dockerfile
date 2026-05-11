FROM dunglas/frankenphp:php8.3-bookworm AS base

WORKDIR /app

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip; \
    install-php-extensions \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        redis \
        zip; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM base AS vendor

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

FROM base AS app

ENV APP_ENV=production \
    APP_DEBUG=false \
    OCTANE_HTTPS=true \
    GENERATE_APP_KEY=true \
    RUN_MIGRATIONS=true \
    RUN_SEEDERS=true \
    RUN_OPTIMIZE=true \
    XDG_CONFIG_HOME=/config \
    XDG_DATA_HOME=/data

COPY . .
COPY --from=vendor /app/vendor ./vendor

RUN set -eux; \
    chmod +x /app/entrypoint.sh; \
    composer dump-autoload --no-dev --classmap-authoritative; \
    php artisan package:discover --ansi; \
    mkdir -p \
        /config \
        /data \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs; \
    chown -R www-data:www-data bootstrap/cache storage /config /data

EXPOSE 80 443 443/udp

ENTRYPOINT ["/app/entrypoint.sh"]
CMD ["start"]
