# Use an official PHP runtime as a parent image
FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
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
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Ensure php-fpm is listening on the correct port
RUN echo "listen = 9000" >> /usr/local/etc/php-fpm.d/www.conf

# Set environment variables in the Docker container
ENV APP_NAME=${APP_NAME}
ENV APP_ENV=${APP_ENV}
ENV APP_KEY=${APP_KEY}
ENV APP_DEBUG=${APP_DEBUG}
ENV APP_URL=${APP_URL}
ENV APP_CLIENT_URL=${APP_CLIENT_URL}
ENV DB_CONNECTION=${DB_CONNECTION}
ENV DB_HOST=${DB_HOST}
ENV DB_PORT=${DB_PORT}
ENV DB_DATABASE=${DB_DATABASE}
ENV DB_USERNAME=${DB_USERNAME}
ENV DB_PASSWORD=${DB_PASSWORD}
ENV SESSION_DRIVER=${SESSION_DRIVER}
ENV SESSION_LIFETIME=${SESSION_LIFETIME}
ENV CACHE_STORE=${CACHE_STORE}
ENV FILESYSTEM_DISK=${FILESYSTEM_DISK}
ENV QUEUE_CONNECTION=${QUEUE_CONNECTION}
ENV AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
ENV AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
ENV AWS_DEFAULT_REGION=${AWS_REGION}
ENV AWS_BUCKET=${AWS_BUCKET}
ENV STRIPE_SECRET_KEY=${STRIPE_SECRET_KEY}
ENV AUTH_GUARD=${AUTH_GUARD}
ENV AUTH_MODEL=${AUTH_MODEL}
ENV AUTH_PASSWORD_BROKER=${AUTH_PASSWORD_BROKER}
ENV AUTH_PASSWORD_RESET_TOKEN_TABLE=${AUTH_PASSWORD_RESET_TOKEN_TABLE}
ENV AUTH_PASSWORD_TIMEOUT=${AUTH_PASSWORD_TIMEOUT}
ENV SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS}
ENV SANCTUM_CSRF_COOKIE=${SANCTUM_CSRF_COOKIE}
ENV MEMCACHED_HOST=${MEMCACHED_HOST}
ENV REDIS_HOST=${REDIS_HOST}
ENV REDIS_PORT=${REDIS_PORT}
ENV REDIS_PASSWORD=${REDIS_PASSWORD}
ENV MAIL_MAILER=${MAIL_MAILER}
ENV MAIL_HOST=${MAIL_HOST}
ENV MAIL_PORT=${MAIL_PORT}
ENV MAIL_USERNAME=${MAIL_USERNAME}
ENV MAIL_PASSWORD=${MAIL_PASSWORD}
ENV MAIL_ENCRYPTION=${MAIL_ENCRYPTION}

# Change current user to www-data
USER www-data

# Ensure proper file permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 and start php-fpm server in non-daemon mode
EXPOSE 9000
CMD ["php-fpm", "--nodaemonize"]
