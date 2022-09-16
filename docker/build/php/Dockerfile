FROM php:8.1-fpm

ARG XDEBUG

RUN apt-get update

RUN apt-get install -y git wget unzip

# extensions
RUN docker-php-ext-install iconv
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN apt-get install libicu-dev -y && docker-php-ext-install intl
RUN apt-get install libzip-dev zip -y && docker-php-ext-install zip
RUN if [ "$XDEBUG" = "TRUE"  ] ; \
        then pecl install xdebug && docker-php-ext-enable xdebug ; \
        else echo Skipping XDebug installation ; \
    fi

# install composer
COPY ./docker/build/php/install-composer.sh /tmp/install-composer.sh
RUN bash /tmp/install-composer.sh

# install dependencies
COPY . /var/www/html
RUN composer install

RUN useradd --create-home --uid 1000 --shell /bin/bash web
RUN chown web. -R /var/www/html
USER web
WORKDIR /var/www/html

COPY ./docker/build/php/docker-php-entrypoint /usr/local/bin/
