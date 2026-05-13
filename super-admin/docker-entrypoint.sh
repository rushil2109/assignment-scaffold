#!/bin/bash
set -e

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    sleep 1
done
echo "MySQL is ready."

php artisan migrate --force

echo "Starting server on port 9001..."
php artisan serve --host=0.0.0.0 --port=9001
