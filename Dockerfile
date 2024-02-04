FROM composer:2

ENV OPENSWOOLE_VERSION 22.1.2

RUN apk add --no-cache openssl openssl-dev linux-headers
RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev
RUN docker-php-ext-install gd sockets

RUN apk add --no-cache --virtual .phpize-deps ${PHPIZE_DEPS} unixodbc-dev && \
    pecl install openswoole-${OPENSWOOLE_VERSION} pcov && \
    docker-php-ext-enable openswoole pcov && \
    apk del .phpize-deps
