# ============================================
# Stage 1: PHP base + extensions
# Layer này hầu như không bao giờ thay đổi → cache vĩnh viễn
# ============================================
FROM php:8.2-apache AS php-base

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev \
    libreoffice-writer libreoffice-core \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# Không chặn cài đặt do security advisory (cho phép Laravel 11.x build được)
RUN composer config --global policy.advisories.block false
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# ============================================
# Stage 2: Composer install
# Chỉ re-run khi composer.json / composer.lock thay đổi
# ============================================
FROM php-base AS vendor-builder

WORKDIR /app

# Bước 2a: Tạo Laravel skeleton (chỉ chạy khi không có composer.lock)
# Dùng file sentinel để biết đã scaffold chưa
COPY composer.json composer.lock* ./

# Nếu composer.lock tồn tại → install; không thì create-project trước
RUN if [ -f "composer.lock" ]; then \
        composer install --no-dev --optimize-autoloader --no-scripts --no-interaction; \
    else \
        composer create-project laravel/laravel:^11 /tmp/laravel-base \
            --prefer-dist --no-interaction --no-audit \
        && cp -r /tmp/laravel-base/vendor ./vendor \
        && cp /tmp/laravel-base/composer.lock ./composer.lock; \
    fi

# ============================================
# Stage 3: Node build
# Chỉ re-run khi package.json / source CSS/JS thay đổi
# ============================================
FROM node:22-alpine AS node-builder

WORKDIR /build

COPY package.json package-lock.json* vite.config.js* tailwind.config.js* postcss.config.js* ./
RUN npm install --legacy-peer-deps

# Copy TOÀN BỘ resources (gồm views) + blade để Tailwind quét ra đủ class.
# (Nếu chỉ copy css/js, Tailwind không thấy blade -> CSS rỗng -> mất style.)
COPY resources ./resources
RUN npm run build

# ============================================
# Stage 4: Runtime - ghép lại
# Chỉ COPY code → rebuild layer cuối rất nhanh (vài giây)
# ============================================
FROM php-base AS runtime

WORKDIR /var/www/html

# Lấy vendor đã build sẵn
COPY --from=vendor-builder /app/vendor ./vendor
COPY --from=vendor-builder /app/composer.lock ./composer.lock

# Lấy assets đã build
COPY --from=node-builder /build/public/build ./public/build

# Copy source code của project (đây là layer thay đổi thường xuyên)
COPY . .

# Nếu chưa có vendor từ copy . . thì dùng cái đã build ở trên
# (COPY sau sẽ ghi đè vendor rỗng từ source → restore lại)
COPY --from=vendor-builder /app/vendor ./vendor

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
