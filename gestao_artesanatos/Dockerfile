FROM php:8.3-apache-bookworm
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
WORKDIR /var/www/app
COPY src/ ./src/
COPY config/app.php config/database.php ./config/
COPY resources/views/layouts/*.php ./resources/views/layouts/
COPY resources/views/pages/ ./resources/views/pages/
COPY public/assets/ ./public/assets/
COPY public/index.php public/.htaccess ./public/
COPY database/schema.sql ./database/schema.sql
COPY bin/ ./bin/
COPY tests/local/ ./tests/local/
COPY deploy/apache.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/php.ini /usr/local/etc/php/conf.d/ong.ini
