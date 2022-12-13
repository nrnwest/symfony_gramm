FROM php:8.1.0-fpm-alpine

# Install packages
RUN apk add --no-cache git curl build-base zlib-dev oniguruma-dev autoconf bash

# Xdebug
ARG INSTALL_XDEBUG=false
RUN if [ ${INSTALL_XDEBUG} = true ]; \
    then \
      pecl install xdebug && docker-php-ext-enable xdebug; \
    fi;

COPY ./_docker/php-fpm/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY ./_docker/php-fpm/default.ini /usr/local/etc/php/conf.d/default.ini

# Postgres && Mysql
RUN apk add --no-cache libpq-dev && docker-php-ext-install pdo_pgsql

# opCache
RUN docker-php-ext-install opcache

# Configure non-root user.
ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

# Source code
RUN chown www-data:www-data /var/www
COPY --chown=www-data:www-data ./ /var/www
WORKDIR /var/www

USER www-data

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

CMD php-fpm

EXPOSE 9000
