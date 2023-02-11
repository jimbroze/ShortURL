FROM php:8.2-apache

# Allow Apache document root to be changed with environment variable
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install PDO for database access
RUN docker-php-ext-install pdo pdo_mysql

# Install zip for composer to get packages
RUN apt-get update && \
    apt-get install -y \
        libzip-dev
RUN docker-php-ext-install zip

# Install composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Prep packages with composer
# composer update must be run inside the running container to update package requirements.
WORKDIR /var/www/html/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install