FROM php:7.2-fpm

RUN apt-get update

RUN apt-get install -y git wget unzip

# extensions
RUN docker-php-ext-install iconv
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN apt-get install libicu-dev -y && docker-php-ext-install intl
RUN apt-get install zlib1g-dev -y && docker-php-ext-install zip
# RUN pecl install xdebug && docker-php-ext-enable xdebug

# install composer
COPY ./docker/build/php/install-composer.sh /tmp/install-composer.sh
RUN bash /tmp/install-composer.sh

# install symfony binary
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# install dependencies
COPY . /var/www/html
RUN composer install

RUN useradd --create-home --uid 1000 --shell /bin/bash web
RUN chown web. -R /var/www/html
USER web
WORKDIR /var/www/html

COPY ./docker/build/php/docker-php-entrypoint /usr/local/bin/
