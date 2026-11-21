# Franken base stage
################################################################################
FROM dunglas/frankenphp:1.13-php8.5-alpine AS frankenphp_base
SHELL ["/bin/sh", "-euxo", "pipefail", "-c"]

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN \
    # Install PHP extensions
    install-php-extensions \
        @composer \
        intl

# Franken dev stage
################################################################################
FROM frankenphp_base AS frankenphp_dev
SHELL ["/bin/sh", "-euxo", "pipefail", "-c"]

RUN \
    # Install OS packages (gnupg2 non è necessario su Alpine)
    apk add --no-cache \
        git \
        gnupg \
        openssh-client \
    ; \
    # Install PHP extensions
    install-php-extensions \
        xdebug \
    ; \
    # Configure PHP
    cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Franken prod stage
################################################################################
FROM frankenphp_base AS frankenphp_prod
SHELL ["/bin/sh", "-euxo", "pipefail", "-c"]

ENV SERVER_NAME=":80"
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV APP_ENV=prod

RUN \
    # Configure PHP
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    { \
        echo "expose_php = 0"; \
        echo "opcache.preload = /app/config/preload.php"; \
        echo "opcache.preload_user = root"; \
    } >> "$PHP_INI_DIR/conf.d/10-php.ini"

COPY --link config/docker/php.ini $PHP_INI_DIR/conf.d/20-php.ini
COPY --link config/docker/php.prod.ini $PHP_INI_DIR/conf.d/30-php.ini
COPY --link composer.* ./

RUN composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link . /app

RUN \
    mkdir -p var/cache var/log; \
    chmod +x bin/console; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    bin/console cache:clear; \
    bin/console assets:install
