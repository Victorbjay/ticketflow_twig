# syntax=docker/dockerfile:1
FROM php:8.3-cli

# System deps for composer dists + intl; PHP extensions commonly used by Twig/Symfony
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       unzip git libicu-dev \
  && docker-php-ext-install intl mbstring \
  && rm -rf /var/lib/apt/lists/*

# Composer (official)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

# Install PHP deps first (better layer caching if you redeploy often)
COPY composer.json composer.lock* /app/
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Now copy the app
COPY . /app

# Ensure data folder exists and is writable (ephemeral on free plan)
RUN mkdir -p /app/data && chmod -R 775 /app/data

# Render provides $PORT; run PHP’s built-in server against /public
ENV PORT=8000
EXPOSE 8000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
