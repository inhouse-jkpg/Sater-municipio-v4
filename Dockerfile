FROM php:8.4.7-fpm

WORKDIR /var/www/html

# PHP settings
RUN { \
        echo 'memory_limit = 256M'; \
        echo 'upload_max_filesize = 64M'; \
        echo 'post_max_size = 64M'; \
        echo 'max_execution_time = 300'; \
        echo 'max_input_vars = 3000'; \
    } > $PHP_INI_DIR/conf.d/custom-php.ini

# Install essential system packages and PHP extensions in one step
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    curl \
    wget \
    default-mysql-client \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libmagickwand-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        intl \
        mbstring \
        exif \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Redis (commonly needed for caching)
RUN pecl install redis && docker-php-ext-enable redis

# Install Imagick (optional but commonly used by WordPress)
RUN pecl install imagick && docker-php-ext-enable imagick

# Install and configure Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configure Xdebug for browser-based debugging
RUN { \
        echo '# Xdebug settings for browser debugging'; \
        echo 'xdebug.mode=develop,debug'; \
        echo 'xdebug.start_with_request=yes'; \
        echo 'xdebug.client_host=host.docker.internal'; \
        echo 'xdebug.client_port=9003'; \
        echo 'xdebug.log=/var/www/html/xdebug.log'; \
        echo 'xdebug.idekey=docker'; \
        echo 'xdebug.max_nesting_level=700'; \
        echo 'xdebug.output_dir=/var/www/html'; \
        echo 'xdebug.show_error_trace=1'; \
        echo 'xdebug.show_exception_trace=1'; \
        echo 'xdebug.var_display_max_depth=10'; \
        echo 'xdebug.var_display_max_children=256'; \
        echo 'xdebug.var_display_max_data=1024'; \
    } > $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini

# Install Composer (pinned version)
ARG COMPOSER_VERSION=2.8.12
RUN curl -fsSL https://getcomposer.org/installer | php -- --version="${COMPOSER_VERSION}" --install-dir=/usr/local/bin --filename=composer

# Install WP-CLI (pinned version)
ARG WP_CLI_VERSION=2.11.0
RUN curl -fsSL -o wp-cli.phar "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar" \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Create a wrapper script for wp-cli
RUN echo '#!/bin/bash' > /usr/local/bin/wp-cli \
    && echo 'wp "$@" --allow-root' >> /usr/local/bin/wp-cli \
    && chmod +x /usr/local/bin/wp-cli

# Clean up
RUN apt-get update && apt-get install -y \
    apt-utils \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY .docker/install-acf-pro.sh /usr/local/bin/install-acf-pro.sh
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/install-acf-pro.sh /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
