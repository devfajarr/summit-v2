#!/bin/bash
set -e

# Ensure directories exist
mkdir -p /var/www/vendor /var/www/node_modules /var/www/storage /var/www/bootstrap/cache /var/www/public/build

# Fix permissions so the dev user (UID 1000) can write to them
echo "Adjusting file permissions..."
chown dev:dev /var/www /var/www/public
chown dev:dev /var/www/composer.lock /var/www/package-lock.json 2>/dev/null || true
chown -R dev:dev /var/www/vendor /var/www/node_modules /var/www/storage /var/www/bootstrap/cache /var/www/public/build

# Run composer install as dev user with HOME set to /home/dev
echo "Installing/updating Composer dependencies..."
HOME=/home/dev runuser -u dev -- composer install --no-interaction --prefer-dist --optimize-autoloader

# Run npm install as dev user
echo "Installing/updating NPM dependencies..."
HOME=/home/dev runuser -u dev -- npm install

# Build assets as dev user
echo "Building assets (npm run build)..."
HOME=/home/dev runuser -u dev -- npm run build

# Run the container command as dev user
echo "Starting application..."
exec runuser -u dev -- "$@"
