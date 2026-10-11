FROM composer:2 AS composer
FROM php:8.4-fpm-bookworm

# 仅构建运行环境，站点代码通过 Compose 挂载，插件安装和代码生成能持久保存。
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
        libzip-dev libonig-dev libxml2-dev libicu-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath pdo_mysql gd mbstring zip dom simplexml xml xmlreader xmlwriter intl opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/zz-badoucms.ini
COPY php-fpm.conf /usr/local/etc/php-fpm.d/zz-badoucms.conf

WORKDIR /var/www/html
CMD ["php-fpm"]
