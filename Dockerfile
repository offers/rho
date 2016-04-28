FROM offers/baseimage:0.2.1

RUN apt-add-repository -y ppa:ondrej/php \
    && export PHP_VERSION=1:7.0+36+deb.sury.org~trusty+1 \
    && export PHP_COMMON_VERSION=1:36+deb.sury.org~trusty+1 \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive \
    apt-get install -qqy --force-yes --no-install-recommends \
                    git \
                    php-cli=$PHP_VERSION \
                    php-common=$PHP_COMMON_VERSION \
                    php7.0-xml \
                    php7.0-zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# phpunit
RUN curl -L -O https://phar.phpunit.de/phpunit.phar \
    && chmod +x phpunit.phar \
    && mv phpunit.phar /usr/local/bin/phpunit

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local
RUN mv /usr/local/composer.phar /usr/local/bin/composer
ADD composer.json /rho/
RUN /bin/bash -l -c "cd /rho && composer install --no-ansi && composer dump-autoload --optimize"

WORKDIR /rho

ADD . /rho

CMD ["/usr/local/bin/phpunit"]
