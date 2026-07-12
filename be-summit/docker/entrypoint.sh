#!/bin/sh
set -e

# Install composer dependencies if they are not already installed or need sync
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies (this might take a moment)..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
else
    echo "Vendor directory exists. Running light composer install to check for changes..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Install npm dependencies if they are not already installed or need sync
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
else
    echo "node_modules exists. Running npm install to check for changes..."
    npm install
fi

# Build assets
echo "Building assets (npm run build)..."
npm run build

# Run the container command
exec "$@"

