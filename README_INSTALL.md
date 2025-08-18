# Instalación de DGSuc App - Rama initial-install

## 🚀 **Instalación Rápida**

### Prerrequisitos

- PHP 8.2 o superior
- Composer 2.0 o superior
- PostgreSQL 12 o superior
- Node.js 18 o superior (para assets)

### 1. **Clonar el Repositorio**

```bash
git clone <repository-url>
cd dgsuc-app
git checkout initial-install
```

### 2. **Instalar Dependencias**

```bash
# Instalar dependencias de PHP
composer install --no-dev --optimize-autoloader

# Instalar dependencias de Node.js (si aplica)
npm install
```

### 3. **Configurar Entorno**

```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar base de datos en .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dgsuc_app
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### 4. **Configurar Base de Datos**

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (opcional)
php artisan db:seed
```

### 5. **Configurar Permisos (Linux)**

```bash
# Configurar permisos de directorios
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Verificar permisos del archivo .env
chmod 644 .env
```

### 6. **Verificar Instalación**

```bash
# Verificar que la aplicación inicia
php artisan serve

# Verificar variables de entorno
php artisan tinker --execute="echo env('APP_KEY');"
```

## 🔧 **Configuración Avanzada**

### Variables de Entorno Adicionales

```env
# Configuración de entorno mejorada
ENV_LOADING_STRATEGY=auto
ENV_VALIDATE_ON_BOOT=true

# Configuración de aplicación
APP_VERSION=1.0
APP_TIMEZONE=America/Santiago
APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_CL
```

### Configuración de Base de Datos

```env
# Conexión principal
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dgsuc_app
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Conexión secundaria (opcional)
DB_CONNECTION_SECONDARY=pgsql
DB_HOST_SECONDARY=127.0.0.1
DB_PORT_SECONDARY=5432
DB_DATABASE_SECONDARY=dgsuc_app_secondary
DB_USERNAME_SECONDARY=tu_usuario
DB_PASSWORD_SECONDARY=tu_password
```

## 🧪 **Pruebas**

### Verificar Funcionamiento

```bash
# Verificar que la aplicación responde
curl http://localhost:8000/up

# Verificar logs
tail -f storage/logs/laravel.log

# Verificar configuración
php artisan config:show
```

### Comandos de Debugging

```bash
# Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Verificar rutas
php artisan route:list

# Verificar migraciones
php artisan migrate:status
```

## 🐳 **Docker (Opcional)**

Si prefieres usar Docker:

```bash
# Construir imagen
docker build -t dgsuc-app .

# Ejecutar contenedor
docker run -p 8000:8000 -v $(pwd):/var/www/html dgsuc-app
```

## 📋 **Solución de Problemas**

### Error: "No application encryption key has been specified"

```bash
# Generar nueva clave
php artisan key:generate

# Verificar que se guardó correctamente
cat .env | grep APP_KEY
```

### Error: "Environment file not found"

```bash
# Verificar que el archivo existe
ls -la .env

# Verificar permisos
chmod 644 .env

# Verificar contenido
head -5 .env
```

### Error: "Database connection failed"

```bash
# Verificar configuración de base de datos
php artisan tinker --execute="echo env('DB_HOST');"

# Probar conexión
php artisan db:show
```

## 📞 **Soporte**

Para problemas adicionales:

1. Revisar logs: `storage/logs/laravel.log`
2. Verificar documentación: `docs/LINUX_ENV_FIX.md`
3. Comprobar configuración: `php artisan config:show`

## 🔄 **Actualizaciones**

Para actualizar la aplicación:

```bash
# Obtener cambios
git pull origin initial-install

# Actualizar dependencias
composer install --no-dev --optimize-autoloader

# Limpiar cachés
php artisan config:clear

# Verificar que funciona
php artisan serve
```

## 📝 **Notas de la Rama**

Esta rama `initial-install` incluye:

- ✅ Solución para problemas de lectura de `.env` en Linux
- ✅ Mejoras en la carga de ServiceProviders
- ✅ Validación robusta de variables de entorno
- ✅ Documentación completa de instalación
- ✅ Compatibilidad con múltiples entornos

---

**Versión:** 1.0  
**Fecha:** $(date)  
**Rama:** initial-install
