FROM php:5.6-apache
WORKDIR /var/www/html

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN pecl install apcu-4.0.10 \
    && docker-php-ext-enable \
            apcu \
            opcache

RUN a2enmod rewrite

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
