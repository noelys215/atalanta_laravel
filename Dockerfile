# Use FrankenPHP as the base image
FROM dunglas/frankenphp:latest

# Install common PHP extensions
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
WORKDIR /var/www/app

# Copy the application code
COPY . /var/www/app

# Set the correct permissions
RUN chown -R www-data:www-data /var/www/app \
    && chmod -R 775 /var/www/app/storage

# Install Composer dependencies
COPY --from=composer:2.6.5 /usr/bin/composer /usr/local/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Expose port 9000 or 80 (whichever is configured in FrankenPHP)
EXPOSE 9000

# Start FrankenPHP directly
CMD ["frankenphp", "--port", "9000", "--workers", "4", "--root", "/var/www/app/public"]
