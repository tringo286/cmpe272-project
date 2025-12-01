# Use official PHP + Apache image
FROM php:8.2-apache

# Install system dependencies for Composer and PHP extensions if needed
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    zip \
    && docker-php-ext-install zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies without dev packages, optimize autoloader
RUN composer install --no-dev --optimize-autoloader

# Copy rest of the application source code
COPY . /var/www/html

# Set proper ownership for Apache
RUN chown -R www-data:www-data /var/www/html

# Enable Apache modules
RUN a2enmod rewrite

# Expose port 80 (Render detects this automatically)
EXPOSE 80

# Default command (from base image) runs Apache in foreground

# Install mysqli extension
RUN docker-php-ext-install mysqli

