# Use PHP 8.2
FROM php:8.2-fpm

# Install common PHP extension dependencies
RUN apt-get update && apt-get install -y \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zlib1g-dev \
    libzip-dev \
    unzip \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    gnupg2 \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip intl mysqli pdo pdo_mysql

# Install Caddy
RUN curl -fsSL https://dl.caddyserver.com | bash

# Set the working directory
COPY . /var/www/app
WORKDIR /var/www/app

RUN chown -R www-data:www-data /var/www/app \
    && chmod -R 775 /var/www/app/storage

# Install Composer
COPY --from=composer:2.6.5 /usr/bin/composer /usr/local/bin/composer

# Copy composer.json to workdir & install dependencies
COPY composer.json ./
RUN composer install

# Copy the Caddyfile to the container
COPY Caddyfile /etc/caddy/Caddyfile

# Set the default command to run Caddy
CMD ["caddy", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]

# Expose the port that the application runs on
EXPOSE 80
