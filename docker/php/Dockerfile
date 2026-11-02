# Versions alignées sur la cible du Raspberry (Debian 13 Trixie) : PHP 8.5, Python 3.13
FROM php:8.5-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev unzip mariadb-client python3 python3-pip \
    && pip3 install --no-cache-dir --break-system-packages mysql-connector-python \
    && docker-php-ext-install mysqli pdo_mysql zip \
    && a2enmod rewrite headers \
    && sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/tz.ini \
    && printf "display_errors=On\nerror_reporting=E_ALL\n" > /usr/local/etc/php/conf.d/dev.ini \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Faux RPi.GPIO : les scripts Python tournent réellement (BDD, fichiers d'état), seules les GPIO sont simulées
COPY python/ /opt/fake_python/
ENV PYTHONPATH=/opt/fake_python

WORKDIR /var/www/html/aqua
