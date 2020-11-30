FROM php:7.2.25-fpm-alpine3.10

RUN docker-php-ext-install mysqli
