# use PHP 8.2
FROM php:8.2-fpm

# Install common php extension dependencies
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
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip intl mysqli pdo pdo_mysql

# Set the working directory
COPY . /var/www/app
WORKDIR /var/www/app

RUN chown -R www-data:www-data /var/www/app \
    && chmod -R 775 /var/www/app/storage

# install composer
COPY --from=composer:2.6.5 /usr/bin/composer /usr/local/bin/composer

# copy composer.json to workdir & install dependencies
COPY composer.json ./
RUN composer install

# Set the default command to run php-fpm
CMD ["sh", "-c", "service nginx start && php-fpm"]

# Expose the port that the application runs on
EXPOSE 9000
