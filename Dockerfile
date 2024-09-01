# Use an official PHP runtime as a parent image
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

# Clean up apt cache to reduce the image size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Copy the Nginx configuration file
COPY nginx.conf /etc/nginx/nginx.conf

# Ensure Nginx directories exist and have correct permissions
RUN mkdir -p /var/lib/nginx && \
    mkdir -p /var/lib/nginx/body && \
    chown -R www-data:www-data /var/lib/nginx && \
    chmod -R 755 /var/lib/nginx

# Expose port 8080 to the outside world
EXPOSE 8080

# Start Nginx and PHP-FPM
CMD service nginx start && php-fpm
