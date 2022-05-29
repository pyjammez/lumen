FROM php:8.1.6-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

RUN apk update \
    && apk add gcc make g++ zlib-dev autoconf

RUN apk add --no-cache --virtual .mongodb-ext-build-deps openssl-dev && \
    pecl install mongodb && \
    pecl clear-cache && \
    apk del .mongodb-ext-build-deps

RUN docker-php-ext-enable mongodb.so

#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# for phpunit
ENV PATH="./vendor/bin:${PATH}"
