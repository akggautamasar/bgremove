# Use an official PHP image with Apache
FROM php:8.2-apache

# Set working directory inside the container
WORKDIR /var/www/html

# Install necessary PHP extensions (curl, gd, etc.)
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

# --- IMPORTANT COPY COMMANDS ---
# 1. Copy the dummy index.html from your 'backend/' folder to the root of the Apache server inside the container.
#    This assumes 'index.html' is directly next to your Dockerfile in the 'backend/' directory.
COPY index.html /var/www/html/index.html

# 2. Copy your PHP application file from 'backend/api/remove-background.php' to the container's 'api/' directory.
#    This assumes 'api/' is a subdirectory of 'backend/', and 'remove-background.php' is inside it.
COPY api/remove-background.php /var/www/html/api/remove-background.php

# Expose port 80 (Apache default)
EXPOSE 80

# Command to run the Apache server in the foreground
CMD ["apache2-foreground"]
