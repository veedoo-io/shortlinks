 Stage 1: Builder
FROM php:8.3-fpm-alpine AS builder

WORKDIR /var/www/html

# Install build dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    build-base

# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    gd \
    bcmath \
    ctype \
    mbstring \
    tokenizer \
    xml \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-progress --no-interaction

# Copy application
COPY . .

# Stage 2: Production
FROM php:8.3-fpm-alpine AS production

WORKDIR /var/www/html

# Install runtime dependencies only
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    postgresql-client \
    mysql-client \
    wget

# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    gd \
    bcmath \
    ctype \
    mbstring \
    tokenizer \
    xml \
    opcache

# Create www-data user (already exists in alpine php image, but ensure proper permissions)
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Copy built application from builder stage
COPY --from=builder --chown=www-data:www-data /var/www/html /var/www/html

# Copy PHP-FPM configuration
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Copy php-fpm health check script
COPY docker/php-fpm-healthcheck /usr/local/bin/php-fpm-healthcheck
RUN chmod +x /usr/local/bin/php-fpm-healthcheck

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=10s --timeout=5s --start-period=5s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

USER www-data

CMD ["php-fpm"]
