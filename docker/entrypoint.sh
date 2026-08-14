#!/bin/sh
set -e

echo "🚀 Starting AI Chat UI..."

# Wait for DB to be truly ready (extra safety)
echo "⏳ Waiting for database..."
until php artisan db:show --json > /dev/null 2>&1; do
    sleep 2
done
echo "✅ Database is ready."

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Run migrations and seed defaults
echo "📦 Running migrations..."
php artisan migrate --force --no-interaction

# Clear and cache config for performance
echo "⚡ Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix storage permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "✅ AI Chat UI is ready at http://localhost:8015"

# Hand off to php-fpm
exec php-fpm
