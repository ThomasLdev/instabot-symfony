FROM php:8.2-fpm

# set main params
ARG BUILD_ARGUMENT_ENV=dev
ENV ENV=$BUILD_ARGUMENT_ENV
ENV APP_HOME /var/www/html
ARG HOST_UID=1000
ARG HOST_GID=1000
ENV USERNAME=www-data
ARG INSIDE_DOCKER_CONTAINER=1
ENV INSIDE_DOCKER_CONTAINER=$INSIDE_DOCKER_CONTAINER
ARG XDEBUG_CONFIG=main
ENV XDEBUG_CONFIG=$XDEBUG_CONFIG
ARG XDEBUG_VERSION=3.3.1
ENV XDEBUG_VERSION=$XDEBUG_VERSION

# check environment
RUN if [ "$BUILD_ARGUMENT_ENV" = "default" ]; then echo "Set BUILD_ARGUMENT_ENV in docker build-args like --build-arg BUILD_ARGUMENT_ENV=dev" && exit 2; \
    elif [ "$BUILD_ARGUMENT_ENV" = "dev" ]; then echo "Building development environment."; \
    elif [ "$BUILD_ARGUMENT_ENV" = "test" ]; then echo "Building test environment."; \
    else echo "Set correct BUILD_ARGUMENT_ENV in docker build-args like --build-arg BUILD_ARGUMENT_ENV=dev. Available choices are dev,test,staging,prod." && exit 2; \
    fi

# install all the dependencies and enable PHP modules
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
      procps \
      nano \
      git \
      unzip \
      libicu-dev \
      zlib1g-dev \
      libxml2 \
      libxml2-dev \
      libreadline-dev \
      cron \
      sudo \
      libzip-dev \
      wget \
      librabbitmq-dev \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
      pdo_mysql \
      sockets \
      intl \
      opcache \
      zip \
    && rm -rf /tmp/* \
    && rm -rf /var/list/apt/* \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# install nodejs and npm
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get -y install nodejs && npm -v

# create document root, fix permissions for www-data user and change owner to www-data
RUN mkdir -p $APP_HOME/public && \
    mkdir -p /home/$USERNAME && chown $USERNAME:$USERNAME /home/$USERNAME \
    && usermod -o -u $HOST_UID $USERNAME -d /home/$USERNAME \
    && groupmod -o -g $HOST_GID $USERNAME \
    && chown -R ${USERNAME}:${USERNAME} $APP_HOME

# put php config for Symfony
COPY ./docker/$BUILD_ARGUMENT_ENV/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/$BUILD_ARGUMENT_ENV/php.ini /usr/local/etc/php/php.ini

# install Xdebug in case dev/test environment
COPY ./docker/general/do_we_need_xdebug.sh /tmp/
COPY ./docker/dev/xdebug-${XDEBUG_CONFIG}.ini /tmp/xdebug.ini
RUN chmod u+x /tmp/do_we_need_xdebug.sh && /tmp/do_we_need_xdebug.sh

# install security-checker in case dev/test environment
COPY ./docker/general/do_we_need_security-checker.sh /tmp/
RUN chmod u+x /tmp/do_we_need_security-checker.sh && /tmp/do_we_need_security-checker.sh

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

# install symfony cli
RUN wget https://get.symfony.com/cli/installer -O - | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# set working directory
WORKDIR $APP_HOME

USER ${USERNAME}

# copy source files
COPY --chown=${USERNAME}:${USERNAME} . $APP_HOME/

# install all PHP dependencies
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-interaction --no-progress --no-suggest

# install JS dependencies
RUN npm install --dev

USER root
