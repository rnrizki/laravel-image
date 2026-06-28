FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor curl \
    && docker-php-ext-install mysqli pdo pdo_mysql

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]