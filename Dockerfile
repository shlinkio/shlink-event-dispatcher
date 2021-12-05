FROM composer:2

ENV OPENSWOOLE_VERSION 4.8.1

RUN apk add --no-cache openssl openssl-dev
RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev
RUN docker-php-ext-install gd

# Install openswoole
# First line fixes an error when installing pecl extensions. Found in https://github.com/docker-library/php/issues/233
RUN apk add --no-cache --virtual .phpize-deps ${PHPIZE_DEPS}

RUN cd /tmp && wget https://pecl.php.net/get/openswoole-${OPENSWOOLE_VERSION}.tgz && \
    tar zxvf openswoole-${OPENSWOOLE_VERSION}.tgz && \
    cd openswoole-${OPENSWOOLE_VERSION} && \
    phpize && \
    ./configure --enable-openssl && \
    make && make install

RUN touch /usr/local/etc/php/conf.d/swoole.ini && \
    echo 'extension=openswoole.so' > /usr/local/etc/php/conf.d/openswoole.ini
