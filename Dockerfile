FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libapache2-mod-authnz-external \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Copy application files
COPY app/ /var/www/html/

# Create symbolic link for share
RUN ln -s /mnt/share /var/www/html/share

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /mnt/share \
    && chmod -R 755 /mnt/share

# Configure Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Configure PHP session security
RUN echo "session.cookie_httponly = 1" >> /usr/local/etc/php/conf.d/session.ini \
    && echo "session.use_strict_mode = 1" >> /usr/local/etc/php/conf.d/session.ini

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
