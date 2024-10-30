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

# Create default certificates directory
RUN mkdir -p /etc/apache2/ssl

# Activate SSL
RUN a2enmod ssl && a2enmod rewrite

# Copy ssl files for apache
COPY ./ssl/fullchain.pem /etc/apache2/ssl/fullchain.pem
COPY ./ssl/privkey.pem /etc/apache2/ssl/privkey.pem

# Copy ssl files for phpmyadmin
COPY ./ssl/fullchain.pem /etc/nginx/ssl/fullchain.pem
COPY ./ssl/privkey.pem /etc/nginx/ssl/privkey.pem

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

