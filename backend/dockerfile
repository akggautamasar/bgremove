# Use an official PHP image with Apache
FROM php:8.2-apache

# Set working directory inside the container
WORKDIR /var/www/html

# Install necessary PHP extensions (curl, etc.)
RUN apt-get update && apt-get install -y \
    curl \
    libjpeg-dev \
    libpng-dev \
    libwebp-dev \
    git \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install curl \
    && docker-php-ext-install pdo pdo_mysql # Add other common extensions if your app uses them

# Remove downloaded packages to keep the image small
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy the dummy index.html to the root of the Apache server.
# This assumes it's located at 'backend/index.html' in your repo.
# If you are placing it in 'backend/api/index.html' then use 'COPY api/index.html /var/www/html/index.html'
COPY index.html /var/www/html/index.html # <-- Assumes index.html is directly in 'backend/'

# Copy your PHP application files into the container's 'api/' directory.
# This assumes remove-background.php is located at 'backend/api/remove-background.php' in your repo.
COPY api/remove-background.php /var/www/html/api/remove-background.php

# Expose port 80
EXPOSE 80

# Command to run the Apache server
CMD ["apache2-foreground"]
