FROMPHP:8.3-APACHE

RUN docher-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enaple mysqli pdo_mysql

RUN a2enomd rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/
	&& chmod -R 755 /var/www/html

EXPOSE 80