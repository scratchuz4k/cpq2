FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

ENV DEBIAN_FRONTEND=noninteractive

# Install NGINX and other dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Copy NGINX configuration
COPY docker/prod/default.conf /etc/nginx/nginx.conf
# Copy existing application directory contents
COPY . /var/www
COPY docker/prod/start-app.sh /usr/local/bin/start-app

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set file permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www
# Give execute permission to start-app.sh
RUN chmod +x /usr/local/bin/start-app

# Expose ports (NGINX listens on 80, PHP-FPM on 9000 internally)
EXPOSE 80

# Migrates and Seeds DB and Starts supervisord to manage both PHP-FPM and Nginx
# CMD ["start-app"]