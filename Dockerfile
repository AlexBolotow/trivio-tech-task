FROM composer:2.10.2@sha256:d020706319701a44468968321dccd0fce6620190159a7a9ec195d78e6e971c71 AS composer

FROM php:8.4.25-fpm-bookworm@sha256:0a5638c2c48eb04987107ae448a398dd18345bcf659e15fd99a422617e754264 AS php-base

WORKDIR /var/www/app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libfcgi-bin \
        unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=mlocati/php-extension-installer:2.11.12@sha256:b6d3fa381b9ba5cf051117c1c601d6a523b590e534bf3d56eb4fbe352949c138 \
    /usr/bin/install-php-extensions \
    /usr/local/bin/install-php-extensions

RUN install-php-extensions \
    bcmath \
    intl \
    opcache \
    pcntl \
    pdo_mysql \
    zip

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY docker/php/conf.d/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

FROM php-base AS development

ENV APP_ENV=local \
    APP_DEBUG=1 \
    COMPOSER_HOME=/tmp/composer

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN install-php-extensions xdebug/xdebug@3.5.3 \
    && groupmod --non-unique --gid "${HOST_GID}" www-data \
    && usermod --non-unique --uid "${HOST_UID}" --gid "${HOST_GID}" www-data \
    && mkdir -p /tmp/composer \
    && chown -R www-data:www-data /var/www/app /tmp/composer

COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

USER www-data

CMD ["php-fpm"]
