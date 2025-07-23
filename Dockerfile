# Dockerfile used by docker-compose.yml
FROM php:8.3-apache

# Build argument for Docker tag (version)
ARG DOCKER_TAG=dev

# Install (but also activate mysqli extension)
RUN docker-php-ext-install mysqli

# Install necessary dependencies
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
# This ensures the container has the application code in all scenarios:
# - In production: code is embedded in the image (no external dependencies)
# - In development: provides initial files and correct permissions before volume mount overrides it
COPY ./src/ /var/www/html/

# Create version file with the Docker tag
RUN echo "$DOCKER_TAG" > /var/www/html/.version

# Expose port HTTP
EXPOSE 80
