FROM php:8.5-apache-bookworm AS php-base

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

ARG PHP_EXTENSION_BUILD_JOBS=1

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        openssl \
        unzip \
        $PHPIZE_DEPS; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"${PHP_EXTENSION_BUILD_JOBS}" \
        bcmath \
        exif \
        gd \
        intl \
        pcntl \
        pdo_mysql \
        zip; \
    for extension in curl mbstring pdo_mysql zip intl gd pcntl "Zend OPcache"; do \
        php -m | grep -Fx "$extension"; \
    done

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    a2enmod headers rewrite ssl; \
    mkdir -p /etc/apache2/ssl; \
    rm -rf /tmp/pear ~/.pearrc

RUN set -eux; \
    { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.jit=off'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini; \
    { \
        echo '<VirtualHost *:80>'; \
        echo '    ServerName ${APACHE_SERVER_NAME}'; \
        echo '    RewriteEngine On'; \
        echo '    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]'; \
        echo '</VirtualHost>'; \
    } > /etc/apache2/sites-available/000-default.conf; \
    { \
        echo '<IfModule mod_ssl.c>'; \
        echo '<VirtualHost *:443>'; \
        echo '    ServerName ${APACHE_SERVER_NAME}'; \
        echo '    DocumentRoot /var/www/html/public'; \
        echo ''; \
        echo '    SSLEngine on'; \
        echo '    SSLCertificateFile /etc/apache2/ssl/server.crt'; \
        echo '    SSLCertificateKeyFile /etc/apache2/ssl/server.key'; \
        echo ''; \
        echo '    <Directory /var/www/html/public>'; \
        echo '        AllowOverride All'; \
        echo '        Options -Indexes +FollowSymLinks'; \
        echo '        Require all granted'; \
        echo '    </Directory>'; \
        echo ''; \
        echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
        echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
        echo '</VirtualHost>'; \
        echo '</IfModule>'; \
    } > /etc/apache2/sites-available/default-ssl.conf; \
    a2ensite default-ssl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM php-base AS vendor

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

FROM php-base AS app

ENV APP_NAME="Defly Manager" \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=https://localhost \
    LOG_CHANNEL=stack \
    LOG_STACK=single \
    LOG_DEPRECATIONS_CHANNEL=null \
    LOG_LEVEL=debug \
    DB_CONNECTION=mysql \
    DB_HOST=127.0.0.1 \
    DB_PORT=3306 \
    DB_DATABASE=defly_manager \
    DB_USERNAME=root \
    DB_PASSWORD="" \
    SESSION_DRIVER=database \
    SESSION_LIFETIME=120 \
    SESSION_ENCRYPT=false \
    SESSION_PATH=/ \
    SESSION_DOMAIN=null \
    BROADCAST_CONNECTION=log \
    FILESYSTEM_DISK=local \
    QUEUE_CONNECTION=database \
    CACHE_STORE=database \
    MAIL_MAILER=resend \
    MAIL_FROM_ADDRESS=onboarding@resend.dev \
    MAIL_FROM_NAME="Defly Manager" \
    RESEND_API_KEY="" \
    RESEND_PATH=resend \
    USER_NAME=root \
    USER_EMAIL=root@defly.2ndproject.site \
    USER_PASSWORD=random \
    TOKEN_LOCATION=header \
    TOKEN_KEY_NAME=X-Token-Key \
    USER_AGENT=Defly/Manager \
    TIMEZONE=Asia/Ho_Chi_Minh \
    API_PREFIX=v1 \
    GUI_PREFIX=defly-manager \
    THEME_COLOR=violet \
    ORCHESTRATOR_BASE_URL=https://orchestrator:8000 \
    ORCHESTRATOR_PATH_PREFIX=api/v1 \
    ORCHESTRATOR_PATH_DEPLOYMENT=deployments \
    ORCHESTRATOR_METHOD_DEPLOY=post \
    ORCHESTRATOR_METHOD_FOLLOW=get \
    ORCHESTRATOR_METHOD_CANCEL=delete \
    ORCHESTRATOR_USERNAME=defly-orchestrator \
    ORCHESTRATOR_PASSWORD=P@55w0rd \
    ORCHESTRATOR_TLS_SKIP_VERIFY=false \
    ORCHESTRATOR_TLS_CERT_FILE=storage/tls/orchestrator/orchestrator.crt \
    ORCHESTRATOR_EMAIL_HEADER_KEY=X-Executor \
    DEFENDER_SERVER_TLS_SKIP_VERIFY=false \
    DEFENDER_SERVER_TLS_DIRECTORY=storage/tls/defenders \
    RUN_MIGRATIONS=true \
    RUN_SEEDERS=true \
    RUN_OPTIMIZE=true \
    FIX_PERMISSIONS=true \
    GENERATE_APP_KEY=true \
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
        storage/logs \
        storage/tls/defenders \
        storage/tls/orchestrator; \
    chown -R www-data:www-data bootstrap/cache storage; \
    chmod 0777 storage/tls/defenders

EXPOSE 80 443

ENTRYPOINT ["/var/www/html/entrypoint.sh"]
CMD ["start"]
