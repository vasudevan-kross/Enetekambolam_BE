FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libapache2-mod-security2 \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable Apache Rewrite and Headers Modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy application source
COPY . .

# Ensure public/.htaccess is copied (redundant after COPY . ., but kept for clarity)
COPY public/.htaccess public/.htaccess

# Install Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Remove old dependencies and lock file to ensure a fresh install
RUN rm -rf vendor composer.lock

# (Optional) Clear Composer cache
RUN composer clear-cache

# Install PHP dependencies fresh
RUN composer install --no-dev --optimize-autoloader

# Ensure uploads directory exists and is writable
RUN mkdir -p /var/www/html/public/uploads/images && \
    chown -R www-data:www-data /var/www/html/public/uploads && \
    chmod -R 775 /var/www/html/public/uploads

# Set proper permissions for Laravel storage
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Clear Laravel caches
RUN php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear

# Set Apache DocumentRoot to /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Optional: Create symbolic link to storage (if you're using Laravel's storage system)
RUN php artisan storage:link || true

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
