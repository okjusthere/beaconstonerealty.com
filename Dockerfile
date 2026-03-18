FROM dunglas/frankenphp:php8.2
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN install-php-extensions gd mysqli mbstring xml curl zip intl pdo_mysql
WORKDIR /app
COPY . .
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --no-scripts; fi
EXPOSE 80
