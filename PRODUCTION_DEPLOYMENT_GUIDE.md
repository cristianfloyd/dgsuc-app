# Guía de Despliegue en Producción - Sistema DGSUC

Esta guía proporciona instrucciones paso a paso para el despliegue del Sistema de Informes y Nóminas en un entorno de producción.

## 🎯 Antes de Comenzar

**Prerrequisitos:**
- Servidor configurado según [DEPLOYMENT_REQUIREMENTS.md](DEPLOYMENT_REQUIREMENTS.md)
- Acceso SSH al servidor de producción
- Credenciales de base de datos PostgreSQL
- Certificados SSL válidos
- Configuración de red y firewall completada

## 📋 Lista de Verificación Pre-Despliegue

### ✅ Servidor Base
- [ ] Sistema operativo actualizado (Ubuntu 22.04 LTS)
- [ ] Usuario `informes-app` creado con permisos adecuados
- [ ] Firewall configurado (puertos 22, 80, 443, 5432)
- [ ] Certificados SSL instalados y configurados

### ✅ Software Base
- [ ] PHP 8.3+ instalado con todas las extensiones
- [ ] PostgreSQL 12+ instalado y configurado
- [ ] Nginx/Apache configurado
- [ ] Redis instalado y configurado
- [ ] Node.js 18+ y npm instalado
- [ ] Composer 2.5+ instalado
- [ ] Supervisor instalado

## 🚀 Proceso de Despliegue

### Paso 1: Preparación del Entorno

```bash
# 1.1 Conectar al servidor
ssh informes-app@servidor-produccion

# 1.2 Crear estructura de directorios
sudo mkdir -p /var/www/informes-app
sudo mkdir -p /var/log/informes-app
sudo mkdir -p /backups/informes

# 1.3 Configurar permisos
sudo chown -R informes-app:www-data /var/www/informes-app
sudo chown informes-app:informes-app /var/log/informes-app
sudo chown informes-app:informes-app /backups/informes
```

### Paso 2: Clonado del Repositorio

```bash
# 2.1 Clonar repositorio (ajustar URL según corresponda)
cd /var/www
sudo git clone https://github.com/cristianfloyd/informes-app.git
sudo chown -R informes-app:www-data informes-app

# 2.2 Acceder al directorio del proyecto
cd /var/www/informes-app

# 2.3 Checkout a rama de producción
git checkout production  # o main según configuración
```

### Paso 3: Instalación de Dependencias

```bash
# 3.1 Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# 3.2 Instalar dependencias Node.js
npm install --production

# 3.3 Compilar assets para producción
npm run build
```

### Paso 4: Configuración del Entorno

```bash
# 4.1 Copiar archivo de configuración base
cp .env.example .env

# 4.2 Generar clave de aplicación
php artisan key:generate

# 4.3 Editar archivo .env con configuraciones de producción
nano .env
```

#### Configuración .env Crítica:
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
DB_PASSWORD=[CONFIGURAR_PASSWORD_SEGURO]

# Base de Datos Mapuche
DB2_HOST=[IP_SERVIDOR_MAPUCHE]
DB2_PORT=5432
DB2_DATABASE=mapuche_prod
DB2_USERNAME=informes_readonly
DB2_PASSWORD=[PASSWORD_READONLY]

# Cache y Colas
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Configuración Microsoft Azure (SSO UBA)
MICROSOFT_CLIENT_ID=[AZURE_CLIENT_ID]
MICROSOFT_CLIENT_SECRET=[AZURE_CLIENT_SECRET]
```

### Paso 5: Configuración de Base de Datos

```bash
# 5.1 Crear base de datos principal
sudo -u postgres psql << EOF
CREATE DATABASE informes_app;
CREATE USER informes_user WITH PASSWORD '[PASSWORD_SEGURO]';
GRANT ALL PRIVILEGES ON DATABASE informes_app TO informes_user;
ALTER USER informes_user CREATEDB;
\q
EOF

# 5.2 Crear esquemas necesarios
php artisan db:create-schemas

# 5.3 Ejecutar migraciones
php artisan migrate --force

# 5.4 Sembrar datos base (si es necesario)
php artisan db:seed --force
```

### Paso 6: Configuración de Permisos Laravel

```bash
# 6.1 Configurar permisos de storage y bootstrap
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 6.2 Asegurar propietario correcto
chown -R informes-app:www-data storage
chown -R informes-app:www-data bootstrap/cache
```

### Paso 7: Optimizaciones Laravel

```bash
# 7.1 Limpiar caches existentes
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 7.2 Generar caches optimizados
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7.3 Optimizaciones Filament
php artisan filament:optimize

# 7.4 Optimizar autoloader
composer dump-autoload --optimize
```

### Paso 8: Configuración de Queue Workers

```bash
# 8.1 Crear configuración Supervisor
sudo tee /etc/supervisor/conf.d/informes-worker.conf << 'EOF'
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
stdout_logfile=/var/log/informes-app/worker.log
stopwaitsecs=3600
EOF

# 8.2 Recargar Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start informes-worker:*
```

### Paso 9: Configuración de Cron Jobs

```bash
# 9.1 Agregar crontab para usuario informes-app
crontab -e
```

Agregar estas líneas:
```bash
# Laravel Scheduler (cada minuto)
* * * * * cd /var/www/informes-app && php artisan schedule:run >> /dev/null 2>&1

# Tareas específicas críticas
0 2 * * * cd /var/www/informes-app && php artisan concepto-listado:refresh
0 3 * * * cd /var/www/informes-app && php artisan fallecidos:refresh
30 2 * * * /usr/local/bin/backup-informes.sh
```

### Paso 10: Configuración del Servidor Web

#### Para Nginx:
```bash
# 10.1 Crear configuración Nginx
sudo tee /etc/nginx/sites-available/informes-app << 'EOF'
server {
    listen 80;
    server_name dgsuc.uba.ar;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name dgsuc.uba.ar;

    root /var/www/informes-app/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 600;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    client_max_body_size 256M;
    client_body_timeout 300s;
}
EOF

# 10.2 Habilitar sitio
sudo ln -s /etc/nginx/sites-available/informes-app /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Paso 11: Script de Backup

```bash
# 11.1 Crear script de backup
sudo tee /usr/local/bin/backup-informes.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/informes"

# Backup de base de datos
pg_dump -h localhost -U informes_user -d informes_app -f "$BACKUP_DIR/informes_app_$DATE.sql"

# Backup de archivos de aplicación
tar -czf "$BACKUP_DIR/app_files_$DATE.tar.gz" \
    --exclude=vendor \
    --exclude=node_modules \
    --exclude=storage/logs \
    /var/www/informes-app

# Limpiar backups antiguos (30 días)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completado: $DATE" >> /var/log/informes-app/backup.log
EOF

# 11.2 Hacer ejecutable
sudo chmod +x /usr/local/bin/backup-informes.sh

# 11.3 Probar backup
sudo /usr/local/bin/backup-informes.sh
```

## ✅ Verificación Post-Despliegue

### Pruebas Funcionales

```bash
# 1. Verificar estado de servicios
systemctl status nginx
systemctl status php8.3-fpm
systemctl status postgresql
systemctl status redis
supervisorctl status

# 2. Probar conectividad de base de datos
php artisan tinker
# En tinker: DB::connection()->getPdo();
# En tinker: DB::connection('pgsql-mapuche')->getPdo();

# 3. Verificar colas
php artisan queue:work --once

# 4. Probar comandos críticos
php artisan concepto-listado:refresh --test
php artisan sicoss:test 12345  # Con un legajo de prueba
```

### Pruebas Web

1. **Acceso principal**: https://dgsuc.uba.ar
2. **Login Microsoft**: Verificar SSO con Azure AD
3. **Paneles principales**:
   - `/admin` - Panel administrativo
   - `/afip` - Panel AFIP/SICOSS
   - `/reportes` - Panel de reportes
   - `/embargos` - Panel de embargos
   - `/bloqueos` - Panel de bloqueos

### Monitoreo Inicial

```bash
# Logs en tiempo real
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
tail -f /var/www/informes-app/storage/logs/laravel.log
tail -f /var/log/informes-app/worker.log

# Verificar procesos
ps aux | grep php
ps aux | grep nginx
ps aux | grep postgres
```

## 🚨 Resolución de Problemas Comunes

### Error de Permisos
```bash
# Reconfigurar permisos
sudo chown -R informes-app:www-data /var/www/informes-app
sudo chmod -R 755 /var/www/informes-app
sudo chmod -R 775 /var/www/informes-app/storage
sudo chmod -R 775 /var/www/informes-app/bootstrap/cache
```

### Error de Base de Datos
```bash
# Verificar conexión PostgreSQL
sudo -u postgres psql -c "SELECT version();"
php artisan tinker
# En tinker: DB::connection()->getPdo();
```

### Error de Cache
```bash
# Limpiar todos los caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear
```

### Queue Workers No Funcionan
```bash
# Reiniciar workers
sudo supervisorctl restart informes-worker:*
sudo supervisorctl status informes-worker:*
```

## 🔄 Proceso de Actualización

### Para Updates de Código

```bash
# 1. Modo mantenimiento
php artisan down --message="Actualizando sistema..." --retry=60

# 2. Backup antes de actualizar
/usr/local/bin/backup-informes.sh

# 3. Actualizar código
git pull origin production

# 4. Instalar dependencias actualizadas
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# 5. Ejecutar migraciones
php artisan migrate --force

# 6. Limpiar y regenerar caches
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# 7. Reiniciar queue workers
sudo supervisorctl restart informes-worker:*

# 8. Salir del modo mantenimiento
php artisan up
```

## 📊 Monitoreo Continuo

### Scripts de Monitoreo

```bash
# Script de verificación de salud del sistema
#!/bin/bash
# /usr/local/bin/health-check.sh

# Verificar servicios críticos
systemctl is-active nginx > /dev/null || echo "NGINX DOWN"
systemctl is-active php8.3-fpm > /dev/null || echo "PHP-FPM DOWN"
systemctl is-active postgresql > /dev/null || echo "POSTGRESQL DOWN"
systemctl is-active redis > /dev/null || echo "REDIS DOWN"

# Verificar queue workers
supervisorctl status informes-worker:* | grep -v RUNNING && echo "WORKER DOWN"

# Verificar conectividad de base de datos
php /var/www/informes-app/artisan tinker --execute="DB::connection()->getPdo();" > /dev/null || echo "DB CONNECTION ERROR"

echo "Health check completed: $(date)"
```

### Alertas Automáticas
```bash
# Agregar a crontab para verificación cada 5 minutos
*/5 * * * * /usr/local/bin/health-check.sh | grep -v "Health check completed" | mail -s "Sistema DGSUC - Alerta" admin@uba.ar
```

---

## 📞 Contacto de Soporte

**En caso de problemas durante el despliegue:**
- Documentar error completo con logs
- Verificar [DEPLOYMENT_REQUIREMENTS.md](DEPLOYMENT_REQUIREMENTS.md)
- Consultar logs del sistema y aplicación
- Contactar equipo de desarrollo con detalles específicos

**Versión de la guía**: 1.0 - Diciembre 2024