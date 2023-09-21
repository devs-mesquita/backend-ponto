FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# COPY composer.lock /var/www/html/
# COPY composer.json /var/www/html
COPY . .
RUN composer install


RUN php artisan config:cache && \
    php artisan route:cache && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite

EXPOSE 80

# CMD ["apache2ctl", "-D", "FOREGROUND"]

CMD ["/bin/bash",  "/var/www/html/scripts.sh"]