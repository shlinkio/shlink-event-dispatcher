FROM composer:1.10.0

ENV SWOOLE_VERSION 4.4.15

RUN apk add --no-cache openssl openssl-dev
RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev
RUN docker-php-ext-install gd

# Install swoole
# First line fixes an error when installing pecl extensions. Found in https://github.com/docker-library/php/issues/233
RUN apk add --no-cache --virtual .phpize-deps ${PHPIZE_DEPS}

RUN cd /tmp && wget https://pecl.php.net/get/swoole-${SWOOLE_VERSION}.tgz && \
    tar zxvf swoole-${SWOOLE_VERSION}.tgz && \
    cd swoole-${SWOOLE_VERSION} && \
    phpize && \
    ./configure --enable-openssl && \
    make && make install

RUN touch /usr/local/etc/php/conf.d/swoole.ini && \
    echo 'extension=swoole.so' > /usr/local/etc/php/conf.d/swoole.ini
