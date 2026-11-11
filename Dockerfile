FROM php:8.3-apache

RUN docker-php-ext-install pdo_sqlite

WORKDIR /var/www/html
COPY . .

RUN mkdir -p data/cache \
    && chown -R www-data:www-data data

EXPOSE 80

