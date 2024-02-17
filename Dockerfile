FROM composer:2

RUN apk add --no-cache openssl openssl-dev linux-headers
RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev
RUN docker-php-ext-install gd sockets

RUN apk add --no-cache --virtual .phpize-deps ${PHPIZE_DEPS} unixodbc-dev && \
    pecl install pcov && \
    docker-php-ext-enable pcov && \
    apk del .phpize-deps
