FROM php:7.2.10-apache-stretch

RUN docker-php-ext-install mysqli
