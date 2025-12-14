# --------------------------------------------------------------------
# 1. On part d'une image officielle PHP avec Apache
#    Tout-en-un : PHP + serveur web Apache intégré
# --------------------------------------------------------------------
FROM php:8.2-apache

# --------------------------------------------------------------------
# 2. On met à jour le système Debian interne et on installe des paquets nécessaires
#    - git, unzip : utiles pour Composer
#    - libicu-dev : utile pour Intl (formatage dates, nombres)
#    - libpq-dev : driver PostgreSQL si besoin
# --------------------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
&& rm -rf /var/lib/apt/lists/*

# --------------------------------------------------------------------
# 3. On active les extensions PHP utiles pour Symfony
#    pdo_pgsql = driver PostgreSQL
#    intl = gestion des dates, formatage
#    opcache = cache de bytecode PHP (performance)
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
# 4. Installer Composer en copiant l'exécutable depuis l'image officielle Composer
# --------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# --------------------------------------------------------------------
# 5. Activer mod_rewrite Apache (nécessaire pour Symfony)
# --------------------------------------------------------------------
RUN a2enmod rewrite

# --------------------------------------------------------------------
# 6. Créer la configuration Apache directement dans le Dockerfile
#    Pas besoin de fichier externe !
# --------------------------------------------------------------------
RUN echo '<VirtualHost *:80>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/public\n\
    \n\
    <Directory /var/www/public>\n\
        AllowOverride All\n\
        Require all granted\n\
        \n\
        # Symfony routing\n\
        FallbackResource /index.php\n\
    </Directory>\n\
    \n\
    <Directory /var/www/public/bundles>\n\
        FallbackResource disabled\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# --------------------------------------------------------------------
# 7. Définir le dossier de travail
# --------------------------------------------------------------------
WORKDIR /var/www

# --------------------------------------------------------------------
# 8. Copier tout le projet Symfony dans le conteneur
# --------------------------------------------------------------------
COPY . .

# --------------------------------------------------------------------
# 9. Installer les dépendances Symfony
#    --no-interaction = pas de questions pendant l'exécution
#    --prefer-dist = téléchargement plus rapide
#    --no-scripts = pas d'exécution des scripts (cache:clear, etc.)
# --------------------------------------------------------------------
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# --------------------------------------------------------------------
# 10. Permissions pour les dossiers Symfony
# --------------------------------------------------------------------
RUN mkdir -p /var/www/var/cache /var/www/var/log \
    && chown -R www-data:www-data /var/www/var \
    && chmod -R 775 /var/www/var

# --------------------------------------------------------------------
# 11. Exposer le port 80
# --------------------------------------------------------------------
EXPOSE 80

# --------------------------------------------------------------------
# 11.5. Script d'entrypoint pour fixer les permissions au démarrage
# --------------------------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

# --------------------------------------------------------------------
# 13. Lancer Apache au démarrage
# --------------------------------------------------------------------
CMD ["apache2-foreground"]