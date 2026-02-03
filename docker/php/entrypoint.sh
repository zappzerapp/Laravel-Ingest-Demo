#!/bin/bash
set -e

echo "Waiting for database to be ready..."

max_tries=30
counter=0

while [ $counter -lt $max_tries ]; do
    if php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT', 3306), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch(PDOException \$e) { exit(1); }" > /dev/null 2>&1; then
        echo "Database is ready!"
        break
    fi
    
    echo "Waiting for database connection... ($counter/$max_tries)"
    sleep 1
    counter=$((counter+1))
done

if [ $counter -eq $max_tries ]; then
    echo "Error: Could not connect to database after $max_tries attempts."
    exit 1
fi

echo "Running migrations..."
php artisan migrate --force

echo "Starting command..."
exec "$@"
