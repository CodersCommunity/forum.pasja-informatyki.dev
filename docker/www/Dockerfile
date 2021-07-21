FROM php:7.1-apache

ARG XDEBUG_REMOTE_ENABLE=1
ARG XDEBUG_REMOTE_CONNECT_BACK=0
ARG XDEBUG_REMOTE_HOST=host.docker.internal
ARG XDEBUG_REMOTE_PORT=9000
ARG XDEBUG_IDEKEY=PHPSTORM
ARG XDEBUG_AUTOSTART=1
ARG XDEBUG_REMOTE_LOG

RUN apt-get update -y
RUN apt-get install -y --no-install-recommends libzip-dev zip unzip git libfreetype6-dev libjpeg62-turbo-dev libpng-dev

RUN docker-php-ext-install mysqli calendar mbstring
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install -j$(nproc) gd
RUN pecl install xdebug-2.9.8 && docker-php-ext-enable xdebug && \
    echo "xdebug.remote_enable=${XDEBUG_REMOTE_ENABLE}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_connect_back=${XDEBUG_REMOTE_CONNECT_BACK}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_host=${XDEBUG_REMOTE_HOST}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_port=${XDEBUG_REMOTE_PORT}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=${XDEBUG_IDEKEY}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.autostart=${XDEBUG_AUTOSTART}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_log=${XDEBUG_REMOTE_LOG}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN curl -o /usr/local/bin/composer https://getcomposer.org/composer.phar && chmod +x /usr/local/bin/composer

RUN echo "sendmail_path=/usr/local/bin/mhsendmail" >> /usr/local/etc/php/conf.d/php.ini

RUN curl -fsSL https://deb.nodesource.com/setup_11.x | bash - && apt-get install -y nodejs

COPY forum.conf /etc/apache2/sites-available/
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod expires
RUN a2dissite 000-default && a2ensite forum.conf

RUN usermod -u 1000 www-data
RUN chown www-data /var/www
