FROM dunglas/frankenphp:php8.2
RUN install-php-extensions gd mysqli mbstring xml curl zip intl pdo_mysql
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY . .
RUN if [ -f composer.json ]; then /usr/local/bin/composer install --no-dev --optimize-autoloader --no-scripts; fi
EXPOSE 80
