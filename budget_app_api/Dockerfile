# --------------------------------------------------------------------
# 1. Image PHP-FPM (sans Apache)
# --------------------------------------------------------------------
FROM php:8.2-fpm

# --------------------------------------------------------------------
# 2. Installation des paquets nécessaires + Nginx
# --------------------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    nginx \
&& rm -rf /var/lib/apt/lists/*

# --------------------------------------------------------------------
# 3. Extensions PHP pour Symfony
# --------------------------------------------------------------------
RUN docker-php-ext-install pdo pdo_pgsql intl opcache

# --------------------------------------------------------------------
# 3.1 Configuration OPcache pour le développement
# --------------------------------------------------------------------
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.revalidate_freq=0'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# --------------------------------------------------------------------
# 4. Installer Composer
# --------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# --------------------------------------------------------------------
# 5. Configuration Nginx pour Symfony
# --------------------------------------------------------------------
RUN echo 'server {\n\
    listen 80;\n\
    server_name localhost;\n\
    root /var/www/public;\n\
    \n\
    location / {\n\
        try_files $uri /index.php$is_args$args;\n\
    }\n\
    \n\
    location ~ ^/index\.php(/|$) {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_split_path_info ^(.+\.php)(/.*)$;\n\
        include fastcgi_params;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        fastcgi_param DOCUMENT_ROOT $realpath_root;\n\
        fastcgi_param HTTP_AUTHORIZATION $http_authorization;\n\
        internal;\n\
    }\n\
    \n\
    location ~ \.php$ {\n\
        return 404;\n\
    }\n\
    \n\
    error_log /var/log/nginx/error.log;\n\
    access_log /var/log/nginx/access.log;\n\
}' > /etc/nginx/sites-available/default

# --------------------------------------------------------------------
# 6. Définir le dossier de travail
# --------------------------------------------------------------------
WORKDIR /var/www

# --------------------------------------------------------------------
# 7. Copier le projet Symfony
# --------------------------------------------------------------------
COPY . .

# --------------------------------------------------------------------
# 8. Installer les dépendances Symfony
# --------------------------------------------------------------------
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# --------------------------------------------------------------------
# 9. Permissions pour Symfony
# --------------------------------------------------------------------
RUN mkdir -p /var/www/var/cache /var/www/var/log \
    && chown -R www-data:www-data /var/www/var \
    && chmod -R 775 /var/www/var

# --------------------------------------------------------------------
# 10. Exposer le port 80
# --------------------------------------------------------------------
EXPOSE 80

# --------------------------------------------------------------------
# 11. Script d'entrypoint
# --------------------------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

# --------------------------------------------------------------------
# 12. Lancer PHP-FPM et Nginx
# --------------------------------------------------------------------
CMD service nginx start && php-fpm