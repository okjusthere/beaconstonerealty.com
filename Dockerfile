FROM dunglas/frankenphp:php8.2

RUN install-php-extensions gd mysqli mbstring xml curl zip intl pdo_mysql

WORKDIR /app

COPY . .

RUN if [ -f composer.json ]; then \
    composer install --no-dev --optimize-autoloader --no-scripts; \
    fi

EXPOSE 80
