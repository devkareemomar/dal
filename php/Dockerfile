FROM php:7.4.1-fpm-alpine

RUN apk add libxml2-dev libzip-dev libpng-dev
RUN docker-php-ext-install pdo pdo_mysql gd zip
RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS}

# RUN pecl install xdebug \
#   && docker-php-ext-enable xdebug

 RUN pecl install apfd \
     && docker-php-ext-enable apfd

 RUN apk del pcre-dev ${PHPIZE_DEPS}

RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN alias composer='php /usr/bin/composer'
