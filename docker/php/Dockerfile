# Image unique de l'application (web Apache, planificateur, service GPIO), deux cibles :
#   dev  : Docker local, code monté en volume, faux RPi.GPIO
#   prod : Raspberry Pi (arm64), code copié dans l'image, rpi-lgpio (vrais GPIO)
# Versions : PHP 8.5, Python 3.13 (Debian 13 Trixie)

FROM php:8.5-apache AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev unzip mariadb-client python3 python3-pip \
    && docker-php-ext-install pdo_mysql zip pcntl \
    && apt-mark manual libzip5 \
    && a2enmod rewrite headers \
    && echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/tz.ini \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/aqua.conf /etc/apache2/conf-enabled/aqua.conf
COPY docker/php/entrypoint.sh /usr/local/bin/aqua-entrypoint
ENV TZ=Europe/Paris PYTHONUNBUFFERED=1
WORKDIR /var/www/html/aqua
ENTRYPOINT ["aqua-entrypoint"]
CMD ["apache2-foreground"]

# --- développement local -------------------------------------------------------
FROM base AS dev
RUN printf "display_errors=On\nerror_reporting=E_ALL\n" > /usr/local/etc/php/conf.d/dev.ini \
    && a2enmod ssl
# Faux RPi.GPIO pour le service GPIO : entrées lues dans /opt/gpio/<broche>, sorties tracées dans var/log/gpio-fake.log
COPY docker/python/ /opt/fake_python/
ENV PYTHONPATH=/opt/fake_python

# --- production (Raspberry Pi) -------------------------------------------------
FROM base AS prod
# rpi-lgpio (API RPi.GPIO compatible noyaux récents) : bibliothèque C lg compilée depuis les sources
ARG LG_VERSION=0.2.2
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl make gcc libc6-dev python3-dev swig \
    && curl -fsSL https://github.com/joan2937/lg/archive/refs/tags/v${LG_VERSION}.tar.gz | tar xz -C /tmp \
    && make -C /tmp/lg-${LG_VERSION} liblgpio.so \
    && install -m 644 /tmp/lg-${LG_VERSION}/liblgpio.so /usr/local/lib/liblgpio.so.1 \
    && ln -sf liblgpio.so.1 /usr/local/lib/liblgpio.so && install -m 644 /tmp/lg-${LG_VERSION}/lgpio.h /usr/local/include/ \
    && ldconfig \
    && pip3 install --no-cache-dir --break-system-packages "lgpio==${LG_VERSION}.0" rpi-lgpio \
    && rm -rf /tmp/lg-${LG_VERSION} \
    && apt-get purge -y curl make gcc python3-dev swig && apt-get autoremove -y && rm -rf /var/lib/apt/lists/* \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf "opcache.enable=1\nopcache.validate_timestamps=0\nopcache.memory_consumption=64\n" > /usr/local/etc/php/conf.d/opcache.ini
ENV APP_ENV=prod APP_DEBUG=0 LG_WD=/tmp
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative \
    && composer run-script --no-dev post-install-cmd \
    && mkdir -p var && chown -R www-data:www-data var
