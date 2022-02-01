FROM nginx/unit:1.26.1-php8.1

EXPOSE 8080

COPY ./nginx-config.json ./docker-entrypoint.d/config.json

RUN set -xe &&				                  \
    timedatectl set-timezone Europe/Moscow

RUN apt update &&                      \
    apt install nano git libzip-dev -y 

RUN set -xe &&				                  \
    export DEBIAN_FRONTEND=noninteractive &&  \
    docker-php-ext-install zip pdo_mysql &&   \
    pecl install xdebug-3.1.2 &&              \
    docker-php-ext-enable xdebug

ADD https://getcomposer.org/installer /tmp/composer
RUN php /tmp/composer --install-dir=/usr/bin --filename=composer
