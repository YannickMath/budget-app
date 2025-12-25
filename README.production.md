# Budget App API - Guide de Déploiement Production

## Table des Matières

1. [Architecture de Production](#architecture-de-production)
2. [Prérequis](#prérequis)
3. [Structure du Projet](#structure-du-projet)
4. [Configuration](#configuration)
5. [Déploiement](#déploiement)
6. [Monitoring & Maintenance](#monitoring--maintenance)
7. [Sécurité](#sécurité)
8. [Performance](#performance)
9. [Troubleshooting](#troubleshooting)

---

## Architecture de Production

### Vue d'ensemble

L'application Budget App API est déployée avec une architecture Docker multi-conteneurs optimisée pour la production :

```
┌─────────────────────────────────────────────────────────────┐
│                        Internet                              │
└────────────────────────┬────────────────────────────────────┘
                         │
                    ┌────▼─────┐
                    │  Nginx   │ (Port 80/443)
                    │  Alpine  │ - SSL/TLS
                    │          │ - Rate Limiting
                    └────┬─────┘ - Gzip
                         │
                    ┌────▼─────┐
                    │ PHP-FPM  │ (Port 9000)
                    │  8.2     │ - OPcache
                    │          │ - Symfony 7.4
                    └─┬──┬─────┘
                      │  │
        ┌─────────────┘  └──────────────┐
        │                               │
   ┌────▼────┐                    ┌────▼────┐
   │PostgreSQL│                    │  Redis  │
   │    16   │                    │    7    │
   │         │                    │         │
   └─────────┘                    └─────────┘
   (Port 5432)                    (Port 6379)
```

### Stack Technique

- **Web Server**: Nginx Alpine (reverse proxy + SSL)
- **Application**: PHP 8.2-FPM avec Symfony 7.4
- **API Platform**: 4.2 (REST/GraphQL)
- **Base de données**: PostgreSQL 16
- **Cache/Sessions**: Redis 7
- **Authentification**: JWT (Lexik Bundle)

---

## Prérequis

### Environnement Serveur

- **OS**: Linux (Ubuntu 22.04 LTS recommandé)
- **Docker**: >= 24.0
- **Docker Compose**: >= 2.20
- **RAM**: Minimum 4GB (8GB recommandé)
- **CPU**: 2 cores minimum
- **Stockage**: 20GB minimum

### Logiciels Requis

```bash
# Installer Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Installer Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Vérifier les installations
docker --version
docker-compose --version
```

### Domaine et DNS

- Nom de domaine configuré (ex: `api.budget-app.com`)
- Enregistrement DNS pointant vers votre serveur
- Certificat SSL (Let's Encrypt recommandé)

---

## Structure du Projet

### Arborescence Complète

```
budget-app/
├── budget_app_api/                 # Application Symfony
│   ├── bin/                        # Exécutables (console)
│   ├── config/                     # Configuration Symfony
│   │   ├── packages/               # Configuration des bundles
│   │   ├── routes/                 # Configuration des routes
│   │   └── services.yaml           # Services
│   ├── migrations/                 # Migrations Doctrine
│   ├── public/                     # Point d'entrée web
│   │   └── index.php
│   ├── src/
│   │   ├── Command/                # Commandes CLI
│   │   ├── Controller/             # Contrôleurs
│   │   │   ├── Auth/               # Authentification
│   │   │   └── User/               # Gestion utilisateurs
│   │   ├── DTO/                    # Data Transfer Objects
│   │   │   ├── RegistrationUser/   # DTOs d'inscription
│   │   │   └── User/               # DTOs utilisateur
│   │   ├── Entity/                 # Entités Doctrine
│   │   ├── EventSubscriber/        # Subscribers d'événements
│   │   ├── Processor/              # API Platform Processors
│   │   ├── Provider/               # API Platform Providers
│   │   ├── Repository/             # Repositories
│   │   ├── Service/                # Services métier
│   │   ├── Trait/                  # Traits réutilisables
│   │   └── Kernel.php
│   ├── templates/                  # Templates Twig
│   ├── var/                        # Cache & logs (runtime)
│   ├── vendor/                     # Dépendances
│   ├── .env                        # Variables d'environnement (template)
│   ├── composer.json               # Dépendances PHP
│   └── symfony.lock
│
├── docker/                         # Configuration Docker
│   ├── nginx/
│   │   ├── nginx.conf              # Configuration Nginx principale
│   │   ├── sites/
│   │   │   ├── default.conf        # Config développement
│   │   │   └── production.conf     # Config production
│   │   └── ssl/                    # Certificats SSL
│   │       ├── cert.pem            # Certificat
│   │       └── key.pem             # Clé privée
│   ├── php/
│   │   ├── Dockerfile.prod         # Dockerfile production
│   │   ├── php.ini                 # Configuration PHP
│   │   ├── php-fpm.conf            # Configuration FPM
│   │   ├── www.conf                # Pool FPM
│   │   └── php-fpm-healthcheck     # Script health check
│   └── postgres/
│       ├── init.sql                # Initialisation DB
│       └── postgres.conf           # Configuration PostgreSQL
│
├── config/                         # Configuration environnement
│   ├── .env.prod                   # Variables production
│   ├── .env.staging                # Variables staging
│   └── secrets/                    # Secrets (GITIGNORED)
│       ├── jwt/
│       │   ├── private.pem         # Clé privée JWT
│       │   └── public.pem          # Clé publique JWT
│       └── database.env            # Credentials DB
│
├── deploy/                         # Scripts de déploiement
│   ├── deploy.sh                   # Script déploiement principal
│   ├── rollback.sh                 # Script de rollback
│   ├── health-check.sh             # Health check
│   └── backup.sh                   # Backup automatique
│
├── logs/                           # Logs centralisés (GITIGNORED)
├── backups/                        # Backups DB (GITIGNORED)
│
├── docker-compose.yml              # Développement
├── docker-compose.prod.yml         # Production
├── docker-compose.staging.yml      # Staging
├── .gitignore
└── README.md
```

---

## Configuration

### 1. Cloner le Projet

```bash
git clone https://github.com/votre-organisation/budget-app.git
cd budget-app
```

### 2. Créer la Structure Docker

#### 2.1 Dockerfile Production

Créer `docker/php/Dockerfile.prod` :

```dockerfile
# Multi-stage build pour optimiser la taille
FROM php:8.2-fpm-alpine AS base

# Installation des dépendances système
RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    fcgi \
    && rm -rf /var/cache/apk/*

# Installation des extensions PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    opcache \
    zip

# Configuration OPcache optimisée pour production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=0'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Créer l'utilisateur www
RUN addgroup -g 1000 www && \
    adduser -D -u 1000 -G www www

WORKDIR /var/www

# ============================================
# Stage: Dependencies
# ============================================
FROM base AS dependencies

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ============================================
# Stage: Production
# ============================================
FROM base AS production

# Copier les dépendances du stage précédent
COPY --from=dependencies /var/www/vendor ./vendor

# Copier le code source
COPY --chown=www:www . .

# Installer et optimiser
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative

# Générer le cache Symfony
RUN APP_ENV=prod composer dump-autoload --optimize --classmap-authoritative && \
    php bin/console cache:clear --env=prod --no-debug && \
    php bin/console cache:warmup --env=prod --no-debug

# Permissions
RUN mkdir -p var/cache var/log && \
    chown -R www:www var && \
    chmod -R 775 var

# Health check script
COPY docker/php/php-fpm-healthcheck /usr/local/bin/
RUN chmod +x /usr/local/bin/php-fpm-healthcheck

USER www

EXPOSE 9000

CMD ["php-fpm"]
```

#### 2.2 Configuration Nginx Production

Créer `docker/nginx/sites/production.conf` :

```nginx
upstream php-fpm {
    server php-fpm:9000;
}

# Redirection HTTP vers HTTPS
server {
    listen 80;
    server_name api.budget-app.com;

    # Let's Encrypt challenge
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 301 https://$server_name$request_uri;
    }
}

# Configuration HTTPS
server {
    listen 443 ssl http2;
    server_name api.budget-app.com;
    root /var/www/public;

    # SSL Configuration
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self'; object-src 'none'" always;

    # Logs
    access_log /var/log/nginx/api_access.log;
    error_log /var/log/nginx/api_error.log warn;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript
               application/json application/javascript application/xml+rss
               application/rss+xml font/truetype font/opentype
               application/vnd.ms-fontobject image/svg+xml;

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;
    limit_req zone=api_limit burst=20 nodelay;

    # Client body size
    client_max_body_size 10M;

    # Timeout settings
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;
    fastcgi_send_timeout 60s;
    fastcgi_read_timeout 60s;

    # Main location
    location / {
        try_files $uri /index.php$is_args$args;
    }

    # PHP-FPM
    location ~ ^/index\.php(/|$) {
        fastcgi_pass php-fpm;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_param HTTP_AUTHORIZATION $http_authorization;

        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;

        internal;
    }

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "OK";
        add_header Content-Type text/plain;
    }

    # Deny access to other PHP files
    location ~ \.php$ {
        return 404;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to sensitive files
    location ~ /(\.env|composer\.json|composer\.lock|symfony\.lock) {
        deny all;
    }
}
```

#### 2.3 Docker Compose Production

Créer `docker-compose.prod.yml` :

```yaml
version: '3.8'

services:
  # Service Nginx
  nginx:
    image: nginx:alpine
    container_name: budget_app_nginx_prod
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./budget_app_api/public:/var/www/public:ro
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf:ro
      - ./docker/nginx/sites/production.conf:/etc/nginx/conf.d/default.conf:ro
      - ./docker/nginx/ssl:/etc/nginx/ssl:ro
      - nginx_logs:/var/log/nginx
    depends_on:
      - php-fpm
    networks:
      - budget_network
    healthcheck:
      test: ["CMD", "wget", "--quiet", "--tries=1", "--spider", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s

  # Service PHP-FPM
  php-fpm:
    build:
      context: ./budget_app_api
      dockerfile: ../docker/php/Dockerfile.prod
      target: production
    container_name: budget_app_php_prod
    restart: always
    volumes:
      - ./budget_app_api:/var/www:cached
      - ./docker/php/php.ini:/usr/local/etc/php/php.ini:ro
      - ./docker/php/php-fpm.conf:/usr/local/etc/php-fpm.conf:ro
      - ./docker/php/www.conf:/usr/local/etc/php-fpm.d/www.conf:ro
      - ./config/secrets/jwt:/var/www/config/jwt:ro
      - php_logs:/var/www/var/log
    environment:
      - APP_ENV=prod
      - APP_DEBUG=0
    env_file:
      - ./config/.env.prod
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - budget_network
    healthcheck:
      test: ["CMD-SHELL", "php-fpm-healthcheck || exit 1"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s

  # Service PostgreSQL
  postgres:
    image: postgres:16-alpine
    container_name: budget_app_postgres_prod
    restart: always
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./docker/postgres/init.sql:/docker-entrypoint-initdb.d/init.sql:ro
      - ./backups:/backups
      - postgres_logs:/var/log/postgresql
    env_file:
      - ./config/secrets/database.env
    networks:
      - budget_network
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U $$POSTGRES_USER -d $$POSTGRES_DB"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s

  # Service Redis
  redis:
    image: redis:7-alpine
    container_name: budget_app_redis_prod
    restart: always
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    command: >
      redis-server
      --appendonly yes
      --requirepass ${REDIS_PASSWORD}
      --maxmemory 256mb
      --maxmemory-policy allkeys-lru
    networks:
      - budget_network
    healthcheck:
      test: ["CMD", "redis-cli", "--raw", "incr", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 20s

volumes:
  postgres_data:
    driver: local
  redis_data:
    driver: local
  nginx_logs:
    driver: local
  php_logs:
    driver: local
  postgres_logs:
    driver: local

networks:
  budget_network:
    driver: bridge
```

### 3. Variables d'Environnement

#### 3.1 Fichier `.env.prod`

Créer `config/.env.prod` :

```env
###> symfony/framework-bundle ###
APP_ENV=prod
APP_SECRET=CHANGEZ_CE_SECRET_AVEC_UNE_VALEUR_ALEATOIRE_64_CARACTERES
APP_DEBUG=0
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL="postgresql://${DB_USER}:${DB_PASSWORD}@postgres:5432/${DB_NAME}?serverVersion=16&charset=utf8"
###< doctrine/doctrine-bundle ###

###> nelmio/cors-bundle ###
CORS_ALLOW_ORIGIN='^https://(www\.)?votre-frontend\.com$'
###< nelmio/cors-bundle ###

###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=${JWT_PASSPHRASE}
JWT_TOKEN_TTL=3600
###< lexik/jwt-authentication-bundle ###

###> redis ###
REDIS_URL=redis://:${REDIS_PASSWORD}@redis:6379
###< redis ###

###> mailer (optionnel) ###
MAILER_DSN=smtp://user:pass@smtp.example.com:587
###< mailer ###
```

#### 3.2 Fichier `database.env`

Créer `config/secrets/database.env` :

```env
POSTGRES_DB=budget_app_prod
POSTGRES_USER=budget_app_user
POSTGRES_PASSWORD=CHANGEZ_MOI_MOT_DE_PASSE_FORT
DB_USER=budget_app_user
DB_PASSWORD=CHANGEZ_MOI_MOT_DE_PASSE_FORT
DB_NAME=budget_app_prod
```

### 4. Générer les Clés JWT

```bash
# Créer le dossier
mkdir -p config/secrets/jwt

# Générer la clé privée
openssl genrsa -out config/secrets/jwt/private.pem -aes256 4096

# Générer la clé publique
openssl rsa -pubout -in config/secrets/jwt/private.pem -out config/secrets/jwt/public.pem

# Sécuriser les permissions
chmod 600 config/secrets/jwt/private.pem
chmod 644 config/secrets/jwt/public.pem
```

### 5. Générer APP_SECRET

```bash
# Générer un secret aléatoire de 64 caractères
openssl rand -hex 32
```

Copiez le résultat dans `APP_SECRET` du fichier `.env.prod`.

### 6. Obtenir un Certificat SSL

#### Option A: Let's Encrypt (Recommandé)

```bash
# Installer Certbot
sudo apt install certbot

# Générer le certificat
sudo certbot certonly --standalone -d api.budget-app.com

# Copier les certificats
sudo cp /etc/letsencrypt/live/api.budget-app.com/fullchain.pem docker/nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/api.budget-app.com/privkey.pem docker/nginx/ssl/key.pem
sudo chmod 644 docker/nginx/ssl/cert.pem
sudo chmod 600 docker/nginx/ssl/key.pem
```

#### Option B: Certificat auto-signé (Développement uniquement)

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem
```

---

## Déploiement

### 1. Script de Déploiement

Créer `deploy/deploy.sh` :

```bash
#!/bin/bash

set -e

echo "🚀 Starting Budget App API deployment..."

# Variables
ENVIRONMENT=${1:-production}
COMPOSE_FILE="docker-compose.${ENVIRONMENT}.yml"
BACKUP_DIR="./backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonctions
log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Vérifications préliminaires
log_info "Checking prerequisites..."

if [ ! -f "$COMPOSE_FILE" ]; then
    log_error "Docker Compose file not found: $COMPOSE_FILE"
    exit 1
fi

if ! command -v docker &> /dev/null; then
    log_error "Docker is not installed"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    log_error "Docker Compose is not installed"
    exit 1
fi

# Créer le dossier de backup
mkdir -p "$BACKUP_DIR"

# Backup de la base de données
log_info "Creating database backup..."
if docker-compose -f "$COMPOSE_FILE" ps postgres | grep -q "Up"; then
    docker-compose -f "$COMPOSE_FILE" exec -T postgres pg_dump \
        -U "$DB_USER" "$DB_NAME" > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql" || {
        log_warn "Database backup failed, continuing anyway..."
    }
    log_info "Backup saved to $BACKUP_DIR/db_backup_$TIMESTAMP.sql"
else
    log_warn "PostgreSQL container not running, skipping backup"
fi

# Pull des dernières images
log_info "Pulling latest images..."
docker-compose -f "$COMPOSE_FILE" pull

# Build des images
log_info "Building application images..."
docker-compose -f "$COMPOSE_FILE" build --no-cache php-fpm

# Stop des anciens conteneurs
log_info "Stopping old containers..."
docker-compose -f "$COMPOSE_FILE" down

# Démarrage des nouveaux conteneurs
log_info "Starting new containers..."
docker-compose -f "$COMPOSE_FILE" up -d

# Attendre que les services soient prêts
log_info "Waiting for services to be ready..."
sleep 15

# Vérifier que les conteneurs sont bien démarrés
if ! docker-compose -f "$COMPOSE_FILE" ps | grep -q "Up"; then
    log_error "Containers failed to start"
    log_info "Rolling back..."
    ./deploy/rollback.sh "$ENVIRONMENT"
    exit 1
fi

# Migrations de base de données
log_info "Running database migrations..."
docker-compose -f "$COMPOSE_FILE" exec -T php-fpm \
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || {
    log_error "Migration failed"
    exit 1
}

# Clear cache
log_info "Clearing and warming up cache..."
docker-compose -f "$COMPOSE_FILE" exec -T php-fpm \
    php bin/console cache:clear --env=prod --no-debug
docker-compose -f "$COMPOSE_FILE" exec -T php-fpm \
    php bin/console cache:warmup --env=prod --no-debug

# Health check
log_info "Running health check..."
sleep 5

HEALTH_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health || echo "000")

if [ "$HEALTH_STATUS" -eq 200 ]; then
    log_info "✅ Deployment successful!"
    log_info "API is running on http://localhost"
else
    log_error "❌ Deployment failed! Health check returned: $HEALTH_STATUS"
    log_info "Rolling back..."
    ./deploy/rollback.sh "$ENVIRONMENT"
    exit 1
fi

# Nettoyage des anciennes images
log_info "Cleaning up old images..."
docker image prune -f

log_info "🎉 Deployment completed successfully!"
log_info "Logs: docker-compose -f $COMPOSE_FILE logs -f"
```

Rendre le script exécutable :

```bash
chmod +x deploy/deploy.sh
```

### 2. Script de Rollback

Créer `deploy/rollback.sh` :

```bash
#!/bin/bash

set -e

echo "🔄 Starting rollback..."

ENVIRONMENT=${1:-production}
COMPOSE_FILE="docker-compose.${ENVIRONMENT}.yml"
BACKUP_DIR="./backups"

# Arrêter les conteneurs actuels
echo "⏹️ Stopping current containers..."
docker-compose -f "$COMPOSE_FILE" down

# Restaurer la dernière sauvegarde
LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/db_backup_*.sql 2>/dev/null | head -n 1)

if [ -n "$LATEST_BACKUP" ]; then
    echo "📦 Restoring database from $LATEST_BACKUP..."
    docker-compose -f "$COMPOSE_FILE" up -d postgres
    sleep 10

    cat "$LATEST_BACKUP" | docker-compose -f "$COMPOSE_FILE" exec -T postgres \
        psql -U "$DB_USER" "$DB_NAME"

    echo "✅ Database restored"
else
    echo "⚠️ No backup found to restore"
fi

# Redémarrer avec l'ancienne version
echo "▶️ Starting previous version..."
docker-compose -f "$COMPOSE_FILE" up -d

echo "✅ Rollback completed"
```

Rendre le script exécutable :

```bash
chmod +x deploy/rollback.sh
```

### 3. Déploiement Initial

```bash
# 1. Cloner le projet
git clone https://github.com/votre-organisation/budget-app.git
cd budget-app

# 2. Configurer les variables d'environnement
# Éditer config/.env.prod et config/secrets/database.env

# 3. Générer les clés JWT
mkdir -p config/secrets/jwt
openssl genrsa -out config/secrets/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/secrets/jwt/private.pem -out config/secrets/jwt/public.pem

# 4. Configurer SSL
# Obtenir les certificats SSL (Let's Encrypt ou auto-signé)

# 5. Lancer le déploiement
./deploy/deploy.sh production
```

### 4. Déploiements Ultérieurs

```bash
# Pull des dernières modifications
git pull origin main

# Déployer
./deploy/deploy.sh production
```

---

## Monitoring & Maintenance

### 1. Logs

#### Voir les logs en temps réel

```bash
# Tous les services
docker-compose -f docker-compose.prod.yml logs -f

# Nginx uniquement
docker-compose -f docker-compose.prod.yml logs -f nginx

# PHP-FPM uniquement
docker-compose -f docker-compose.prod.yml logs -f php-fpm

# PostgreSQL uniquement
docker-compose -f docker-compose.prod.yml logs -f postgres
```

#### Logs Symfony

```bash
# Logs de l'application
docker-compose -f docker-compose.prod.yml exec php-fpm tail -f var/log/prod.log

# Logs spécifiques
docker-compose -f docker-compose.prod.yml exec php-fpm ls -la var/log/
```

### 2. Backups Automatiques

Créer `deploy/backup.sh` :

```bash
#!/bin/bash

BACKUP_DIR="/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Créer le backup
docker-compose -f docker-compose.prod.yml exec -T postgres pg_dump \
    -U "$DB_USER" "$DB_NAME" | gzip > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql.gz"

# Supprimer les backups de plus de 30 jours
find "$BACKUP_DIR" -name "db_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup completed: db_backup_$TIMESTAMP.sql.gz"
```

#### Configurer un Cron Job

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne pour un backup quotidien à 2h du matin
0 2 * * * /path/to/budget-app/deploy/backup.sh >> /var/log/budget-app-backup.log 2>&1
```

### 3. Health Checks

Créer `deploy/health-check.sh` :

```bash
#!/bin/bash

API_URL="https://api.budget-app.com"

# Test de l'endpoint health
HEALTH=$(curl -s -o /dev/null -w "%{http_code}" "$API_URL/health")

if [ "$HEALTH" -eq 200 ]; then
    echo "✅ API is healthy"
    exit 0
else
    echo "❌ API is unhealthy (HTTP $HEALTH)"
    # Envoyer une alerte (email, Slack, etc.)
    exit 1
fi
```

### 4. Monitoring avec Prometheus (Optionnel)

Ajouter au `docker-compose.prod.yml` :

```yaml
  prometheus:
    image: prom/prometheus:latest
    container_name: budget_app_prometheus
    restart: always
    ports:
      - "9090:9090"
    volumes:
      - ./docker/prometheus/prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    networks:
      - budget_network

  grafana:
    image: grafana/grafana:latest
    container_name: budget_app_grafana
    restart: always
    ports:
      - "3000:3000"
    volumes:
      - grafana_data:/var/lib/grafana
    networks:
      - budget_network
```

---

## Sécurité

### 1. Checklist de Sécurité

- [x] **HTTPS obligatoire** avec certificat SSL valide
- [x] **Secrets externalisés** (pas dans le code)
- [x] **APP_DEBUG=0** en production
- [x] **JWT avec clés fortes** (4096 bits)
- [x] **Rate limiting** configuré sur Nginx
- [x] **Security headers** (HSTS, CSP, X-Frame-Options)
- [x] **CORS** correctement configuré
- [x] **Pare-feu** (UFW ou iptables)
- [ ] **Monitoring des logs** de sécurité
- [ ] **Updates régulières** des dépendances
- [ ] **Scans de vulnérabilités** (Snyk, OWASP Dependency Check)

### 2. Hardening du Serveur

```bash
# Configurer le pare-feu
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Désactiver l'accès root SSH
sudo sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
sudo systemctl restart ssh

# Installer fail2ban
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. Rotation des Secrets

```bash
# Régénérer les clés JWT tous les 6 mois
openssl genrsa -out config/secrets/jwt/private.pem.new -aes256 4096
openssl rsa -pubout -in config/secrets/jwt/private.pem.new -out config/secrets/jwt/public.pem.new

# Tester avec les nouvelles clés
# Puis remplacer les anciennes
mv config/secrets/jwt/private.pem.new config/secrets/jwt/private.pem
mv config/secrets/jwt/public.pem.new config/secrets/jwt/public.pem

# Redémarrer l'application
./deploy/deploy.sh production
```

### 4. Mises à Jour de Sécurité

```bash
# Mettre à jour Composer régulièrement
docker-compose -f docker-compose.prod.yml exec php-fpm composer update --no-dev

# Vérifier les vulnérabilités
docker-compose -f docker-compose.prod.yml exec php-fpm composer audit

# Mettre à jour les images Docker
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d
```

---

## Performance

### 1. Optimisations PHP

Configuration dans `docker/php/php.ini` :

```ini
[PHP]
; Production settings
memory_limit = 256M
max_execution_time = 60
max_input_time = 60
post_max_size = 10M
upload_max_filesize = 10M

; OPcache
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1

; Realpath cache
realpath_cache_size = 4M
realpath_cache_ttl = 600

; Disable unnecessary extensions
expose_php = Off
```

### 2. Optimisations Nginx

```nginx
# Mise en cache statique
location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# Buffer settings
client_body_buffer_size 128k;
client_max_body_size 10M;
client_header_buffer_size 1k;
large_client_header_buffers 4 16k;
```

### 3. Optimisations PostgreSQL

Configuration dans `docker/postgres/postgres.conf` :

```conf
# Memory settings
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
work_mem = 16MB

# Checkpoint settings
checkpoint_completion_target = 0.9
wal_buffers = 16MB

# Query planner
random_page_cost = 1.1
effective_io_concurrency = 200

# Logging
log_min_duration_statement = 1000
```

### 4. Monitoring des Performances

```bash
# Vérifier l'utilisation des ressources
docker stats

# Analyser les logs lents de PostgreSQL
docker-compose -f docker-compose.prod.yml exec postgres \
    psql -U "$DB_USER" -d "$DB_NAME" -c \
    "SELECT query, calls, total_time, mean_time FROM pg_stat_statements ORDER BY mean_time DESC LIMIT 10;"

# Profiler Symfony avec Blackfire (optionnel)
# Installer l'agent Blackfire et le probe PHP
```

---

## Troubleshooting

### Problèmes Courants

#### 1. L'API ne répond pas

```bash
# Vérifier les conteneurs
docker-compose -f docker-compose.prod.yml ps

# Vérifier les logs
docker-compose -f docker-compose.prod.yml logs nginx php-fpm

# Tester la connectivité
curl -I http://localhost/health
```

#### 2. Erreur 502 Bad Gateway

```bash
# Vérifier que PHP-FPM est bien démarré
docker-compose -f docker-compose.prod.yml ps php-fpm

# Vérifier les logs PHP-FPM
docker-compose -f docker-compose.prod.yml logs php-fpm

# Redémarrer PHP-FPM
docker-compose -f docker-compose.prod.yml restart php-fpm
```

#### 3. Erreur de connexion à la base de données

```bash
# Vérifier que PostgreSQL est démarré
docker-compose -f docker-compose.prod.yml ps postgres

# Vérifier les credentials
docker-compose -f docker-compose.prod.yml exec php-fpm \
    php bin/console doctrine:query:sql "SELECT 1"

# Tester la connexion directement
docker-compose -f docker-compose.prod.yml exec postgres \
    psql -U "$DB_USER" -d "$DB_NAME"
```

#### 4. Permissions refusées

```bash
# Corriger les permissions du dossier var
docker-compose -f docker-compose.prod.yml exec php-fpm \
    chown -R www:www var

# Recreate cache
docker-compose -f docker-compose.prod.yml exec php-fpm \
    php bin/console cache:clear --env=prod
```

#### 5. JWT invalide

```bash
# Vérifier les clés JWT
ls -l config/secrets/jwt/

# Vérifier la passphrase
docker-compose -f docker-compose.prod.yml exec php-fpm \
    openssl rsa -in config/jwt/private.pem -check

# Regénérer les clés si nécessaire
```

### Commandes Utiles

```bash
# Entrer dans le conteneur PHP
docker-compose -f docker-compose.prod.yml exec php-fpm sh

# Exécuter une commande Symfony
docker-compose -f docker-compose.prod.yml exec php-fpm \
    php bin/console <commande>

# Voir les routes
docker-compose -f docker-compose.prod.yml exec php-fpm \
    php bin/console debug:router

# Clear cache
docker-compose -f docker-compose.prod.yml exec php-fpm \
    php bin/console cache:clear --env=prod

# Rebuild complet
docker-compose -f docker-compose.prod.yml down -v
docker-compose -f docker-compose.prod.yml build --no-cache
docker-compose -f docker-compose.prod.yml up -d
```

---

## CI/CD

### GitHub Actions

Créer `.github/workflows/deploy.yml` :

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd /path/to/budget-app
          git pull origin main
          ./deploy/deploy.sh production
```

---

## Support

Pour toute question ou problème :

- **Issues**: https://github.com/votre-organisation/budget-app/issues
- **Documentation Symfony**: https://symfony.com/doc/current/index.html
- **Documentation API Platform**: https://api-platform.com/docs/

---

## Licence

Propriétaire - Tous droits réservés
