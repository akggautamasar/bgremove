# Use an official PHP image with Apache (or Nginx if preferred)
# We choose a version that has GD built-in or is easy to install extensions.
# php:8.2-apache is a good starting point as it includes Apache and PHP-FPM setup.
FROM php:8.2-apache

# Set working directory inside the container
WORKDIR /var/www/html

# Install necessary extensions and packages for image processing.
# 'gd' is crucial for image manipulation functions in PHP.
# You might also need 'php-imagick' if you prefer ImageMagick over GD.
# 'libjpeg-dev', 'libpng-dev', 'libwebp-dev' are dependencies for GD.
RUN apt-get update && apt-get install -y \
    libjpeg-dev \
    libpng-dev \
    libwebp-dev \
    git \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql # Common extensions, add others if your PHP needs them

# Remove downloaded packages to keep the image small
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy your backend files into the container's web root
# This assumes your PHP file is in 'backend/api/' relative to Dockerfile context
COPY backend/api/ /var/www/html/api/

# Configure Apache to rewrite requests (if needed for clean URLs or specific routing)
# For a simple API endpoint, you might not strictly need this if accessing directly via /api/remove-background.php
# However, for a cleaner setup, it's good practice.
# Here, we set the DocumentRoot to /var/www/html/ so /api/remove-background.php is directly accessible.
# You might need to create a custom Apache config for more complex routing if your app needs it.
# For this simple API, directly accessing /api/remove-background.php works.

# Enable Apache rewrite module if you plan to use .htaccess for URL rewriting
# RUN a2enmod rewrite

# Expose port 80 for web traffic (Apache default)
EXPOSE 80

# Command to run the Apache server
CMD ["apache2-foreground"]

# Note on CORS:
# Ensure your PHP script (remove-background.php) still explicitly sets CORS headers.
# This Dockerfile configures the environment but doesn't override application-level CORS.
