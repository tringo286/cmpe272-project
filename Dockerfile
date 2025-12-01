# Use official PHP + Apache image
FROM php:8.2-apache

# Enable useful Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application source
COPY . /var/www/html

# Optional: set proper permissions (good for mounted volumes, CI, etc.)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (Render detects this automatically)
EXPOSE 80

# Default command (from base image) runs Apache in the foreground
