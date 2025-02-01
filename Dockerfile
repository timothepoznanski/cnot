# Dockerfile used by docker-compose.yml
FROM php:7.4-apache

# Install (but also activate mysqli extension)
RUN docker-php-ext-install mysqli

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    vim \
    && docker-php-ext-install zip

# Copy apache config
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy php.ini
COPY php.ini /usr/local/etc/php/

# Copy src files
COPY ./src/ /var/www/html/

# Give permissions to apache
RUN chown -R www-data:www-data /var/www/html/

# Expose port HTTP and HTTPS
EXPOSE 80
EXPOSE 443

