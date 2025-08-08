# Requerimientos Técnicos para Producción - Sistema DGSUC

Este documento especifica los requerimientos técnicos necesarios para el despliegue en producción del Sistema de Informes y Controles de la Universidad de Buenos Aires.

## 📋 Información General

- **Aplicación**: Sistema DGSUC - Informes y Controles UBA
- **Framework**: Laravel 11.x
- **Versión PHP**: 8.3+
- **Base de Datos**: PostgreSQL 17+
- **Arquitectura**: Multi-base de datos con conexiones multiples

## 🖥️ Requerimientos del Servidor

### Sistema Operativo
- **Recomendado**: Ubuntu 24.04 LTS / CentOS 9+ / RHEL 8+
- **Alternativo**: Debian 11+ / Rocky Linux 8+
- **Arquitectura**: x64 (64-bit)
- **Kernel**: Linux 6.12+

### Hardware Mínimo
```
CPU: 4 cores @ 2.4 GHz
RAM: 8 GB DDR4
Almacenamiento: 500 GB SSD
Red: 1 Gbps
```

### Hardware Recomendado (Producción)
```
CPU: 8 cores @ 3.0 GHz (Intel Xeon/AMD EPYC)
RAM: 16 GB DDR4 ECC
Almacenamiento: 1 TB NVMe SSD (RAID 10)
Red: 1 Gbps con redundancia
Backup: Sistema de backup automático
```

## 🐘 PHP - Configuraciones Requeridas

### Versión y Extensiones
```bash
# PHP 8.3+ con las siguientes extensiones
php >= 8.3
php-cli
php-fpm
php-common
php-curl
php-gd
php-intl
php-json
php-mbstring
php-pgsql
php-pdo
php-pdo-pgsql
php-xml
php-zip
php-bcmath
php-fileinfo
php-tokenizer
php-ctype
php-openssl
php-redis (para cache)
```

### Configuración php.ini (Producción)
```ini
# Configuraciones críticas
memory_limit = 2048M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 300
max_input_vars = 10000

# Para procesamiento SICOSS (1.2M+ registros)
max_execution_time = 600
memory_limit = 4096M

# Seguridad
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

# Performance
opcache.enable = 1
opcache.memory_consumption = 512
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
```

## 🗄️ PostgreSQL - Configuración de Base de Datos

### Versión y Configuración
```yaml
PostgreSQL: >= 16.0 (Recomendado 17.x)
Extensiones Requeridas:
  - pg_stat_statements
  - pgcrypto
  - uuid-ossp
```

### Configuración postgresql.conf
```bash
# Memoria y Performance
shared_buffers = 4GB                    # 25% de RAM total
effective_cache_size = 12GB             # 75% de RAM total
work_mem = 256MB                        # Para consultas complejas SICOSS
maintenance_work_mem = 1GB

# Conexiones
max_connections = 100
superuser_reserved_connections = 3

# Logging y Auditoría
log_statement = 'mod'
log_min_duration_statement = 1000
log_checkpoints = on
log_connections = on
log_disconnections = on

# Performance para cargas masivas
checkpoint_completion_target = 0.9
wal_buffers = 64MB
```

### Esquemas de Base de Datos Requeridos
```sql
-- Base de Datos Principal (informes_app)
CREATE DATABASE informes_app;
CREATE SCHEMA suc_app;
CREATE SCHEMA informes_app;

-- Configuración de search_path
ALTER DATABASE informes_app SET search_path = 'suc_app,informes_app,public';

-- Base de Datos Mapuche (Conexión de solo lectura)
-- Configurada en servidor separado o mismo servidor con usuario de solo lectura
GRANT SELECT ON ALL TABLES IN SCHEMA mapuche TO informes_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA suc TO informes_readonly;
```

## 🌐 Servidor Web

### Nginx (Recomendado)
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name dgsuc.uba.ar;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name dgsuc.uba.ar;

    root /var/www/informes-app/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # PHP Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 600;  # Para procesos largos SICOSS
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Laravel Routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Archivos estáticos con caché
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Límites para uploads
    client_max_body_size 256M;
    client_body_timeout 300s;
}
```

### Apache (Alternativo)
```apache
<VirtualHost *:443>
    ServerName dgsuc.uba.ar
    DocumentRoot /var/www/informes-app/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # PHP Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    # Laravel Configuration
    <Directory /var/www/informes-app/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Limits
    LimitRequestBody 268435456  # 256MB
    TimeOut 600
</VirtualHost>
```

## 🚀 Redis - Cache y Sesiones

### Configuración Redis
```bash
# Instalación
Redis Server >= 6.0

# Configuración /etc/redis/redis.conf
maxmemory 4gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000

# Para cache de aplicación
databases 2
# DB 0: Cache de aplicación
# DB 1: Sesiones
```

## 📦 Dependencias del Sistema

### Node.js y NPM (Para Build de Assets)
```bash
Node.js >= 18.x
npm >= 9.x
# O alternativamente
Yarn >= 1.22.x
```

### Composer (PHP Package Manager)
```bash
Composer >= 2.5.x
```

### Herramientas Adicionales
```bash
# Control de versiones
git >= 2.30

# Supervisor para Queue Workers
supervisor

# Utilidades de sistema
curl
wget
unzip
vim/nano
htop
```

## 🔒 Seguridad y Permisos

### Usuarios y Permisos
```bash
# Usuario de aplicación
useradd -m -s /bin/bash informes-app
usermod -a -G www-data informes-app

# Permisos de directorios
chown -R informes-app:www-data /var/www/informes-app
chmod -R 755 /var/www/informes-app
chmod -R 775 /var/www/informes-app/storage
chmod -R 775 /var/www/informes-app/bootstrap/cache
```

### Firewall Básico
```bash
# UFW Configuration
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw allow 5432/tcp  # PostgreSQL (solo desde IPs específicas)
ufw enable
```

## 🔧 Variables de Entorno Críticas

### Archivo .env de Producción
```env
# Aplicación
APP_NAME="Sistema DGSUC"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dgsuc.uba.ar

# Base de Datos Principal
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=informes_app
DB_USERNAME=informes_user
DB_PASSWORD=[STRONG_PASSWORD]
DB_SCHEMA=suc_app

# Base de Datos Mapuche (Solo lectura)
DB2_HOST=[MAPUCHE_SERVER_IP]
DB2_PORT=5432
DB2_DATABASE=mapuche_prod
DB2_USERNAME=informes_readonly
DB2_PASSWORD=[READONLY_PASSWORD]
DB2_CHARSET=SQL_ASCII

# Cache y Sesiones
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=[SMTP_SERVER]
MAIL_PORT=587
MAIL_USERNAME=[MAIL_USER]
MAIL_PASSWORD=[MAIL_PASSWORD]
MAIL_ENCRYPTION=tls

# Microsoft Azure AD (SSO UBA)
MICROSOFT_CLIENT_ID=[AZURE_CLIENT_ID]
MICROSOFT_CLIENT_SECRET=[AZURE_CLIENT_SECRET]
MICROSOFT_REDIRECT_URI=https://dgsuc.uba.ar/auth/microsoft/callback
```

## ⚡ Configuración de Colas (Queue Workers)

### Supervisor Configuration
```ini
# /etc/supervisor/conf.d/informes-worker.conf
[program:informes-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/informes-app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
directory=/var/www/informes-app
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=informes-app
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/informes-worker.log
stopwaitsecs=3600
```

### Cron Jobs para Laravel Scheduler
```bash
# Crontab para usuario informes-app
* * * * * cd /var/www/informes-app && php artisan schedule:run >> /dev/null 2>&1

# Tareas específicas críticas
0 2 * * * cd /var/www/informes-app && php artisan concepto-listado:refresh
0 3 * * * cd /var/www/informes-app && php artisan fallecidos:refresh
```

## 📊 Monitoreo y Logs

### Configuración de Logs
```bash
# Ubicaciones de logs
/var/log/nginx/access.log
/var/log/nginx/error.log
/var/log/php8.3-fpm.log
/var/log/postgresql/postgresql-14-main.log
/var/www/informes-app/storage/logs/laravel.log
```

### Monitoreo de Performance
```bash
# Herramientas recomendadas
htop              # Monitoreo de sistema
pg_stat_statements # PostgreSQL query analysis  
redis-cli monitor # Redis monitoring
tail -f logs      # Monitoreo en tiempo real
```

## 🔄 Backup y Recuperación

### Backup Automático de Base de Datos
```bash
#!/bin/bash
# /usr/local/bin/backup-informes.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/informes"

# Backup de base de datos principal
pg_dump -h localhost -U postgres -d informes_app -f "$BACKUP_DIR/informes_app_$DATE.sql"

# Backup de archivos de aplicación (sin vendor y node_modules)
tar -czf "$BACKUP_DIR/app_files_$DATE.tar.gz" \
    --exclude=vendor \
    --exclude=node_modules \
    --exclude=storage/logs \
    /var/www/informes-app

# Retener solo backups de los últimos 30 días
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

### Cron para Backups
```bash
# Backup diario a las 2:30 AM
30 2 * * * /usr/local/bin/backup-informes.sh
```

## 📈 Optimizaciones de Performance

### Laravel Optimizations
```bash
# En producción, ejecutar siempre:
php artisan config:cache
php artisan route:cache  
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

# Para updates sin downtime:
php artisan config:clear
php artisan config:cache
```

### Base de Datos - Índices Críticos
```sql
-- Índices para performance en tablas críticas
CREATE INDEX CONCURRENTLY idx_afip_sicoss_periodo ON afip_mapuche_sicoss(periodo_fiscal);
CREATE INDEX CONCURRENTLY idx_dh21_legajo_cargo ON dh21(nro_legaj, nro_cargo);
CREATE INDEX CONCURRENTLY idx_embargos_activos ON emb_embargo(nro_legaj) WHERE id_estado_embargo = 1;

-- Índices para reportes
CREATE INDEX CONCURRENTLY idx_reportes_periodo ON concepto_listado(periodo_fiscal, codc_uacad);
```

## 🚨 Consideraciones Especiales

### Deprecated - por migración a backend python fastAPI
### Procesamiento SICOSS (Crítico)
- **Memoria PHP**: Mínimo 4GB para procesamiento de 1.2M+ registros
- **Timeout**: 600 segundos para operaciones SICOSS  
- **Queue Workers**: Dedicados para procesos SICOSS
- **Conexión DB**: Pool de conexiones optimizado para consultas masivas

### Seguridad Específica UBA
- **Red Institucional**: Acceso restringido a red UBA
- **SSO Integration**: Microsoft Azure AD configurado
- **Auditoría**: Logs detallados de acceso y operaciones críticas
- **Backup**: Cifrado y almacenamiento seguro según políticas UBA

### Integración con Sistema Mapuche
- **Conexión Solo Lectura**: Usuario con permisos mínimos
- **Conexion RW**: usuario suc, full access esquema suc
- **Conexion Bloqueos**: usuario bloqueos, permisos rw dh20 y dh03 campos determinados
- **Encoding**: Manejo específico de SQL_ASCII y UTF8
- **Timeout de Conexión**: Configurado para consultas largas
- **Failover**: Configuración de respaldo en caso de caída

---

**Importante**: Este documento debe actualizarse con cada cambio significativo en los requerimientos del sistema. Versión del documento: 1.0 - Fecha: Julio 2025.