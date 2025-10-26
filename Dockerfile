# syntax=docker/dockerfile:1
FROM php:8.3-cli

# System deps for composer dists + intl/mbstring
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       unzip git libicu-dev libonig-dev pkg-config \
  && docker-php-ext-install -j"$(nproc)" intl mbstring \
  && rm -rf /var/lib/apt/lists/*

# Composer (official)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

# Install PHP deps first for better caching
COPY composer.json composer.lock* /app/
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Copy the rest of the app
COPY . /app

# Ensure a writable data dir (ephemeral on free plan)
RUN mkdir -p /app/data && chmod -R 775 /app/data

# Serve /public via PHP built-in server
ENV PORT=8000
EXPOSE 8000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
