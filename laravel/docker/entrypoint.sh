#!/bin/bash
set -e

cd /var/www/html

# Tạo sẵn các thư mục storage (volume storage/app có thể trống lúc mới tạo)
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
         storage/logs storage/app/public storage/app/private bootstrap/cache 2>/dev/null || true

# Đảm bảo quyền storage đúng cho www-data mỗi lần khởi động
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Generate app key nếu chưa có
if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    php artisan key:generate --force
fi

# Chờ MySQL sẵn sàng
echo "Chờ MySQL..."
for i in $(seq 1 30); do
    php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null && break
    echo "  MySQL chưa sẵn sàng, thử lại ($i/30)..."
    sleep 2
done

# Migrate + seed (chỉ lần đầu)
php artisan migrate --seed --force 2>/dev/null || php artisan migrate --force

# Storage link
php artisan storage:link --force 2>/dev/null || true

# Discover packages (vì build dùng --no-scripts, chưa chạy package:discover)
php artisan package:discover --ansi 2>/dev/null || true

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== QMS Forms sẵn sàng tại http://localhost:8088 ==="

# Start Apache
apache2-foreground
