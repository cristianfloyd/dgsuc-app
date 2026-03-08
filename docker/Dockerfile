# DGSUC - PHP 8.4-FPM para Laravel + Nginx
FROM php:8.4-fpm-bookworm

ARG UID=1000
ARG GID=1000

ENV DEBIAN_FRONTEND=noninteractive
ENV PHP_MEMORY_LIMIT=512M

# Dependencias del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP (Laravel + Filament + Excel + DomPDF + PostgreSQL)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    bcmath \
    curl \
    dom \
    gd \
    mbstring \
    intl \
    zip \
    exif \
    opcache

# Redis vía PECL
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Usuario appuser con UID/GID del host (para permisos con volumen montado)
RUN groupadd -g ${GID} appuser 2>/dev/null || true \
    && useradd -u ${UID} -g appuser -m -s /bin/bash appuser 2>/dev/null || true \
    && mkdir -p /var/www/html \
    && chown -R appuser:appuser /var/www/html

# Pool PHP-FPM como appuser (reemplaza www.conf por defecto)
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

# Git: evitar "dubious ownership" en el repo montado
RUN git config --global --add safe.directory /var/www/html

EXPOSE 9000
