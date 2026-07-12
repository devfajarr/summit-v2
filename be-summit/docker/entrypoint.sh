#!/bin/bash
set -e

# Ensure directories exist
mkdir -p /var/www/vendor /var/www/node_modules /var/www/storage /var/www/bootstrap/cache

# Fix permissions so the dev user (UID 1000) can write to them
echo "Adjusting file permissions..."
chown dev:dev /var/www
chown dev:dev /var/www/composer.lock /var/www/package-lock.json 2>/dev/null || true
chown -R dev:dev /var/www/vendor /var/www/node_modules /var/www/storage /var/www/bootstrap/cache

# Run composer install as dev user with login shell to preserve HOME env
echo "Installing/updating Composer dependencies..."
su - dev -c "cd /var/www && composer install --no-interaction --prefer-dist --optimize-autoloader"

# Run npm install as dev user
echo "Installing/updating NPM dependencies..."
su - dev -c "cd /var/www && npm install"

# Build assets as dev user
echo "Building assets (npm run build)..."
su - dev -c "cd /var/www && npm run build"

# Run the container command as dev user
echo "Starting application..."
exec su - dev -c "cd /var/www && exec $@"
