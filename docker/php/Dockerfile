# Imagen base con PHP-FPM 8.2
FROM php:8.2-fpm

# Variables para instalar drivers de SQL Server
ENV ACCEPT_EULA=Y \
    DEBIAN_FRONTEND=noninteractive

# Dependencias del sistema
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       gnupg2 apt-transport-https ca-certificates \
       unixodbc unixodbc-dev libgssapi-krb5-2 curl git zip unzip \
    && curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft-prod.gpg \
    && echo "deb [arch=amd64] https://packages.microsoft.com/ubuntu/22.04/prod jammy main" > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends msodbcsql18 mssql-tools18 \
    && ln -s /opt/mssql-tools18/bin/sqlcmd /usr/bin/sqlcmd \
    && ln -s /opt/mssql-tools18/bin/bcp /usr/bin/bcp \
    && docker-php-ext-install pdo \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuración PHP útil para Laravel
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit=512M" > $PHP_INI_DIR/conf.d/memory.ini \
    && echo "upload_max_filesize=32M" > $PHP_INI_DIR/conf.d/uploads.ini \
    && echo "post_max_size=32M" >> $PHP_INI_DIR/conf.d/uploads.ini \
    && echo "max_execution_time=120" > $PHP_INI_DIR/conf.d/execution.ini

WORKDIR /var/www/html

# Usuario no root (opcional)
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
USER www-data

CMD ["php-fpm"]
