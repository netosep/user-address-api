FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set up a non-root user
RUN useradd -ms /bin/bash user-address-api
USER user-address-api

# Set the working directory
WORKDIR /var/www/app

# Copy project files
COPY --chown=user-address-api:user-address-api . .

# Install dependencies
RUN composer install --no-dev

# Expose port and start php-fpm
EXPOSE 9000
CMD ["php-fpm"]