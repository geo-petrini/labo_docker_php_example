FROM php:8.5-apache

RUN docker-php-ext-install pdo pdo_mysql && a2enmod rewrite

COPY app/ /var/www/html/

EXPOSE 80