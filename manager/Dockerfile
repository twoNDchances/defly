FROM php:8.5-apache-bookworm AS base

WORKDIR /var/www/html

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        openssl \
        unzip \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libsqlite3-dev \
        libzip-dev \
        $PHPIZE_DEPS; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        zip; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    a2enmod headers rewrite ssl; \
    mkdir -p /etc/apache2/ssl; \
    printf '%s\n' \
        '<VirtualHost *:80>' \
        '    ServerName ${APACHE_SERVER_NAME}' \
        '    RewriteEngine On' \
        '    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]' \
        '</VirtualHost>' \
        > /etc/apache2/sites-available/000-default.conf; \
    printf '%s\n' \
        '<IfModule mod_ssl.c>' \
        '<VirtualHost *:443>' \
        '    ServerName ${APACHE_SERVER_NAME}' \
        '    DocumentRoot /var/www/html/public' \
        '' \
        '    SSLEngine on' \
        '    SSLCertificateFile /etc/apache2/ssl/server.crt' \
        '    SSLCertificateKeyFile /etc/apache2/ssl/server.key' \
        '' \
        '    <Directory /var/www/html/public>' \
        '        AllowOverride All' \
        '        Require all granted' \
        '    </Directory>' \
        '' \
        '    ErrorLog ${APACHE_LOG_DIR}/error.log' \
        '    CustomLog ${APACHE_LOG_DIR}/access.log combined' \
        '</VirtualHost>' \
        '</IfModule>' \
        > /etc/apache2/sites-available/default-ssl.conf; \
    a2ensite default-ssl; \
    rm -rf /var/lib/apt/lists/* /tmp/pear

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
    RUN_MIGRATIONS=true \
    RUN_SEEDERS=true \
    RUN_OPTIMIZE=true \
    APACHE_SERVER_NAME=localhost

COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor

RUN set -eux; \
    chmod +x /var/www/html/entrypoint.sh; \
    composer dump-autoload --no-dev --classmap-authoritative; \
    php artisan package:discover --ansi; \
    mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs; \
    chown -R www-data:www-data bootstrap/cache storage

EXPOSE 80 443

ENTRYPOINT ["/var/www/html/entrypoint.sh"]
CMD ["start"]
