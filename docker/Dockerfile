FROM php:7.4-apache

# Install necessary extensions
RUN docker-php-ext-install mysqli

# Copy Apache configuration
COPY /docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Create the uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 777 /var/www/html/uploads

# Set working directory
WORKDIR /var/www/html

# Copy source code into the container
COPY src/ /var/www/html/

# Expose port 80 for Apache
EXPOSE 80