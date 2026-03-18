FROM php:7.4-cli

# Install PHP extensions with dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo_mysql mbstring gd zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Copy app files
WORKDIR /var/www/html
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts 2>/dev/null; true

EXPOSE 80

# Use PHP built-in server with index.php as router
CMD php -S 0.0.0.0:${PORT:-80} index.php
