FROM php:8-apache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Imagick
RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*
RUN mkdir -p /usr/src/php/ext/imagick; \
    curl -fsSL https://github.com/Imagick/imagick/archive/06116aa24b76edaf6b1693198f79e6c295eda8a9.tar.gz | tar xvz -C "/usr/src/php/ext/imagick" --strip 1; \
    docker-php-ext-install imagick;
RUN apt-get autoclean

# CURL
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && a2enmod rewrite

# Source
COPY ./ /var/www/html/
RUN chmod -R 777 /var/www/html

# Dependencies
RUN composer install --no-dev --profile --ignore-platform-reqs

EXPOSE 80

# Startup
CMD ["apache2-foreground"]