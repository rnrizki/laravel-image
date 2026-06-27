FROM jkaninda/nginx-php-fpm:8.3

COPY . /var/www/html

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html

VOLUME /var/www/html/storage

WORKDIR /var/www/html
