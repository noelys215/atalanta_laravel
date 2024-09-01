# Use the official FrankenPHP image as the base image
FROM dunglas/frankenphp:1.2.0

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

# Copy the Caddyfile to the correct location
COPY Caddyfile /etc/caddy/Caddyfile

# Expose the port that FrankenPHP listens on (default is 80)
EXPOSE 80

# Start FrankenPHP (Caddy) without specifying the port (it defaults to 80)
CMD ["caddy", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
