# syntax=docker/dockerfile:1
FROM php:8.3-cli

WORKDIR /app

# Install composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app files
COPY . /app

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Ensure data folder exists & is writable (for users.json / tickets.json)
RUN mkdir -p /app/data && chmod -R 775 /app/data

# Render sets $PORT; run PHP server against /public
ENV PORT=8000
EXPOSE 8000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
