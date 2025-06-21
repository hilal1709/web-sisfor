#!/bin/bash

# Install composer dependencies
composer install --no-dev --optimize-autoloader

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage directories if they don't exist
mkdir -p /tmp/storage/framework/{sessions,views,cache}
mkdir -p /tmp/storage/logs

# Set proper permissions
chmod -R 755 /tmp/storage