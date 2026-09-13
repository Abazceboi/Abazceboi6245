# Dockerfile for INNOVATIONX (PHP 8.2 + TypeScript + PostgreSQL + Apache)
# Optimized for Railway.app & Render

FROM php:8.2-apache

# 1. Install system dependencies & Node.js
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    curl \
    git \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql opcache \
    && a2enmod rewrite

# 2. Install Node.js 20 & npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3. Set Working Directory
WORKDIR /var/www/html

# 4. Copy package files and install dependencies
COPY package*.json tsconfig.json ./
RUN npm install

# 5. Copy source files
COPY . .

# 6. Build TypeScript to JavaScript
RUN npm run build

# 7. Configure Apache port from environment
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

ENV PORT=8080
EXPOSE 8080

CMD ["apache2-foreground"]
