# zxbshop PHP 后端 —— PHP-FPM 运行时镜像
# 只装运行时和扩展，代码通过 docker compose 的 bind-mount 挂进来，
# 这样 nginx（静态文件）和 php-fpm（执行）看到的是同一棵树。
FROM php:8.1-fpm

# ★ 换阿里云 Debian 源。
#   官方 deb.debian.org 在国内极慢（9.6MB 的包索引发起 48 秒后被 Ign 重试，
#   138 秒仍未拿到），不换源这一步会一直卡住甚至构建失败。
#   Debian 12+ 用 deb822 格式的 /etc/apt/sources.list.d/debian.sources，
#   老版本用 /etc/apt/sources.list，两个都处理。
RUN set -eux; \
    for f in /etc/apt/sources.list /etc/apt/sources.list.d/debian.sources; do \
        if [ -f "$f" ]; then \
            sed -i \
              -e 's|https\?://deb.debian.org|http://mirrors.aliyun.com|g' \
              -e 's|https\?://security.debian.org|http://mirrors.aliyun.com|g' \
              "$f"; \
        fi; \
    done; \
    grep -rn 'aliyun' /etc/apt/sources.list /etc/apt/sources.list.d/ 2>/dev/null || true

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg62-turbo-dev \
        libfreetype6-dev libonig-dev libxml2-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring gd zip bcmath exif opcache \
    && (pecl channel-update pecl.php.net || true) \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-zxbshop.ini

WORKDIR /var/www/html
EXPOSE 9000
CMD ["php-fpm"]
