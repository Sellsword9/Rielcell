FROM php:8.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    curl \
    gnupg \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Enable Apache mods
RUN a2enmod rewrite cgi alias env

# Add custom auth script
COPY ./auth /usr/local/bin/git-auth
RUN chmod +x /usr/local/bin/git-auth

# Apache config for Symfony
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js (LTS version)
#RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
 #   apt-get install -y nodejs && \
 #   npm install -g npm

# Set workdir
WORKDIR /var/www/html

# Copy Symfony project
COPY ./symfony /var/www/html

# Install Symfony PHP dependencies
# RUN composer install --no-interaction

# Install Encore & React (JS dependencies)
# WORKDIR /var/www/html
# RUN npm install --save react react-dom
# RUN npm install --save-dev @symfony/webpack-encore babel-preset-react webpack webpack-cli

# Run Encore to build assets (can also be done at runtime or via volume mount in dev)
# RUN npm run build

# Symfony cache clear
# RUN php bin/console cache:clear --no-warmup

# Permissions
RUN usermod -u 1000 www-data

EXPOSE 80
