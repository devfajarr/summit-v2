#!/bin/bash
set -e

# Ensure directories exist
mkdir -p /var/www/vendor /var/www/node_modules /var/www/storage /var/www/bootstrap/cache /var/www/public/build

# Fix permissions so the dev user (UID 1000) can write to them
echo "Adjusting file permissions..."
chown dev:dev /var/www /var/www/public
chown dev:dev /var/www/composer.lock /var/www/package-lock.json 2>/dev/null || true
chown -R dev:dev /var/www/vendor /var/www/node_modules /var/www/storage /var/www/bootstrap/cache /var/www/public/build

# Automatically copy .env.example to .env if .env does not exist
if [ ! -f "/var/www/.env" ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/.env.example /var/www/.env
    chown dev:dev /var/www/.env
fi

# Run composer install as dev user with HOME set to /home/dev
echo "Installing/updating Composer dependencies..."
HOME=/home/dev runuser -u dev -- composer install --no-interaction --prefer-dist --optimize-autoloader

# Run npm install as dev user
echo "Installing/updating NPM dependencies..."
HOME=/home/dev runuser -u dev -- npm install

# Build assets as dev user
echo "Building assets (npm run build)..."
HOME=/home/dev runuser -u dev -- npm run build

# Generate APP_KEY if empty in .env
if ! grep -q "^APP_KEY=base64:" /var/www/.env; then
    echo "Generating APP_KEY..."
    HOME=/home/dev runuser -u dev -- php artisan key:generate --force
fi

# Wait for database connection using PHP to verify PDO availability
echo "Waiting for database connection..."
HOME=/home/dev runuser -u dev -- php << 'EOF'
$host = 'laravel-mysql';
$port = 3306;
$user = 'root';
$pass = '';
$db = 'be_summit';
if (file_exists('.env')) {
    foreach (file('.env') as $line) {
        if (preg_match('/^DB_HOST=(.+)/', trim($line), $m)) $host = trim($m[1], "\"'");
        if (preg_match('/^DB_PORT=(.+)/', trim($line), $m)) $port = trim($m[1], "\"'");
        if (preg_match('/^DB_USERNAME=(.+)/', trim($line), $m)) $user = trim($m[1], "\"'");
        if (preg_match('/^DB_PASSWORD=(.*)/', trim($line), $m)) $pass = trim($m[1], "\"'");
        if (preg_match('/^DB_DATABASE=(.+)/', trim($line), $m)) $db = trim($m[1], "\"'");
    }
}
for ($i = 0; $i < 30; $i++) {
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
        echo "Database is connected!\n";
        exit(0);
    } catch (Exception $e) {
        echo "Waiting for database to start... ($i)\n";
        sleep(2);
    }
}
exit(1);
EOF

# Run database migrations
echo "Running database migrations..."
HOME=/home/dev runuser -u dev -- php artisan migrate --force

# Seed database conditionally (only if admin@example.com doesn't exist)
echo "Checking if database needs seeding..."
if HOME=/home/dev runuser -u dev -- php << 'EOF'
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    if (!Illuminate\Support\Facades\DB::table('users')->where('email', 'admin@example.com')->exists()) {
        exit(0);
    }
} catch (Exception $e) {}
exit(1);
EOF
then
    echo "Admin user not found. Running database seeders..."
    HOME=/home/dev runuser -u dev -- php artisan db:seed --force
else
    echo "Database already seeded. Skipping seeders."
fi

# Generate L5 Swagger documentation
echo "Generating L5 Swagger documentation..."
HOME=/home/dev runuser -u dev -- php artisan l5-swagger:generate

# Run the container command
echo "Starting application..."
exec "$@"
