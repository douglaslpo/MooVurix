# Dockerfile para Moodle
FROM php:8.2-apache

# Informações de build
LABEL maintainer="Moodle DevOps Team"
LABEL description="Moodle LMS - Ambiente de Desenvolvimento e Testes"
LABEL version="4.0"

# Argumentos de build
ARG DEBIAN_FRONTEND=noninteractive

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    # Bibliotecas gerais
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libxslt1-dev \
    libpq-dev \
    libldap2-dev \
    # Ferramentas
    git \
    unzip \
    curl \
    vim \
    cron \
    postgresql-client \
    # Limpeza
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        opcache \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        soap \
        xsl \
        ldap \
        exif \
        fileinfo

# Instalar extensões PECL
RUN pecl install redis && docker-php-ext-enable redis

# Configurar PHP
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'upload_max_filesize = 100M'; \
    echo 'post_max_size = 100M'; \
    echo 'max_execution_time = 300'; \
    echo 'max_input_vars = 5000'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 256'; \
    echo 'opcache.max_accelerated_files = 20000'; \
    echo 'opcache.revalidate_freq = 60'; \
    echo 'date.timezone = America/Sao_Paulo'; \
} > /usr/local/etc/php/conf.d/moodle.ini

# Configurar Apache
RUN a2enmod rewrite expires headers ssl

# Criar diretório de dados do Moodle
RUN mkdir -p /var/moodledata && \
    chown -R www-data:www-data /var/moodledata && \
    chmod -R 777 /var/moodledata

# Configurar DocumentRoot do Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar scripts de inicialização
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Definir working directory
WORKDIR /var/www/html

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html

# Expor porta
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
  CMD curl -f http://localhost/ || exit 1

# Entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

