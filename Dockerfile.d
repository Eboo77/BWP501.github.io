FROM php:8.3-apache

RUN docker-php-ext-install pgsqli pdo pdo_pgsql && docker-php-ext-enable pgsqli pdo_pgsql

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/ && chmod -R 755 /var/www/html

EXPOSE 80