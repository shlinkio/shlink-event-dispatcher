FROM composer:2

ENV OPENSWOOLE_VERSION 4.12.0

RUN apk add --no-cache openssl openssl-dev
RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev
RUN docker-php-ext-install gd sockets

RUN apk add --no-cache --virtual .phpize-deps ${PHPIZE_DEPS} unixodbc-dev && \
    pecl install openswoole-${OPENSWOOLE_VERSION} && \
    docker-php-ext-enable openswoole && \
    apk del .phpize-deps
