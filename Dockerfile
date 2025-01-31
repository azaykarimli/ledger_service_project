# Use the official PHP 8.3 CLI image
FROM php:8.3-cli

# Set the working directory
WORKDIR /var/www/html


# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql zip intl



# Ensure Git recognizes the working directory as safe
RUN git config --global --add safe.directory /var/www/html

# Copy composer from the official composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application code
COPY . .

# Set the environment to production
ENV APP_ENV=prod

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Clear cache manually after installation
RUN rm -rf var/cache/*

# Expose port 9000
EXPOSE 9000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
