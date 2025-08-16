# 📚 Documentación de Configuración de Bases de Datos

## 📋 Tabla de Contenidos
1. [Descripción General](#descripción-general)
2. [Arquitectura de Servidores](#arquitectura-de-servidores)
3. [Configuración de Variables de Entorno](#configuración-de-variables-de-entorno)
4. [Conexiones de Base de Datos](#conexiones-de-base-de-datos)
5. [Configuración Técnica](#configuración-técnica)
6. [Guías de Uso](#guías-de-uso)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Descripción General

Esta aplicación utiliza múltiples conexiones de base de datos PostgreSQL organizadas por servidores y entornos. La configuración está diseñada para soportar desarrollo, testing, producción y consultas especializadas.

### **Características Principales**
- **Múltiples entornos**: Desarrollo, testing, producción y consultas
- **Configuración centralizada**: Variables de entorno organizadas por servidor
- **Encoding consistente**: `SQL_ASCII` charset con `utf8` collate
- **Escalabilidad**: Fácil agregar nuevas conexiones siguiendo el patrón establecido

---

## 🏗️ Arquitectura de Servidores

### **Servidor Principal** (`DB_*`)
- **Propósito**: Base de datos principal de la aplicación
- **Puerto**: 5432
- **Schema**: `suc_app, informes_app`

### **Servidor de Testing y Desarrollo** (`DB_TEST_*`)
- **Propósito**: Desarrollo, testing y bases de datos de respaldo
- **Puerto**: 5434
- **Bases de datos**: `desa`, `liqui`, `mapuche`, `suc`, `2503`, `2504`, `2505`, `2506`
- **Schema**: `mapuche,suc`

### **Servidor de Backup Post-Producción** (`DB_TEST_R2_*`)
- **Propósito**: Bases de datos de respaldo y post-producción
- **Puerto**: 5435
- **Bases de datos**: `2507`, `2508`, `2509`, `2510`, `2511`, `2512`
- **Schema**: `mapuche,suc`

### **Servidor de Desarrollo Local** (`DB_LOCAL_*`)
- **Propósito**: Desarrollo y testing local
- **Puerto**: 5432
- **Base de datos**: `desa-alt`
- **Schema**: `mapuche,suc`

### **Servidor de Producción Antiguo** (`DB_PROD_*`)
- **Propósito**: Producción antigua (legacy)
- **Puerto**: 5434
- **Base de datos**: `mapuche`
- **Schema**: `mapuche,suc`

### **Servidor de Producción Actual** (`DB_PROD_R2_*`)
- **Propósito**: Producción actual
- **Puerto**: 5434
- **Base de datos**: `mapuche`
- **Schema**: `mapuche,suc`

### **Servidor de Consultas** (`DB_CONSULTA_*`)
- **Propósito**: Consultas especializadas y reportes
- **Puerto**: 5435
- **Base de datos**: `mapuche`
- **Schema**: `mapuche,suc`

---

## ⚙️ Configuración de Variables de Entorno

### **Archivo `.env.example`**

```env
# ========================================
# BASE DE DATOS PRINCIPAL
# ========================================
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=suc_app
DB_USERNAME=postgres
DB_PASSWORD=1234
DB_CHARSET=utf8
DB_SCHEMA=suc_app

# ========================================
# SERVIDOR DE TESTING Y DESARROLLO (DB_TEST)
# ========================================
DB_TEST_HOST=127.0.0.1
DB_TEST_PORT=5434
DB_TEST_DATABASE=liqui
DB_TEST_USERNAME=postgres
DB_TEST_PASSWORD=1234
DB_TEST_CHARSET=utf8

# ========================================
# SERVIDOR DE BACKUP POST-PRODUCCIÓN (DB_TEST_R2)
# ========================================
DB_TEST_R2_HOST=127.0.0.1
DB_TEST_R2_PORT=5435
DB_TEST_R2_USERNAME=postgres
DB_TEST_R2_PASSWORD=1234
DB_TEST_R2_CHARSET=utf8

# ========================================
# DESARROLLO Y TESTING LOCAL (DB_LOCAL)
# ========================================
DB_LOCAL_HOST=127.0.0.1
DB_LOCAL_PORT=5432
DB_LOCAL_DATABASE=desa
DB_LOCAL_USERNAME=postgres
DB_LOCAL_PASSWORD=1234
DB_LOCAL_CHARSET=utf8

# ========================================
# SERVIDOR DE PRODUCCIÓN ANTIGUO (DB_PROD)
# ========================================
DB_PROD_HOST=127.0.0.1
DB_PROD_PORT=5434
DB_PROD_DATABASE=mapuche
DB_PROD_USERNAME=postgres
DB_PROD_PASSWORD=1234
DB_PROD_CHARSET=utf8

# ========================================
# SERVIDOR DE PRODUCCIÓN ACTUAL (DB_PROD_R2)
# ========================================
DB_PROD_R2_HOST=127.0.0.1
DB_PROD_R2_PORT=5434
DB_PROD_R2_DATABASE=mapuche
DB_PROD_R2_USERNAME=postgres
DB_PROD_R2_PASSWORD=1234
DB_PROD_R2_CHARSET=utf8

# ========================================
# SERVIDOR DE CONSULTAS (DB_CONSULTA)
# ========================================
DB_CONSULTA_HOST=127.0.0.1
DB_CONSULTA_PORT=5435
DB_CONSULTA_DATABASE=mapuche
DB_CONSULTA_USERNAME=postgres
DB_CONSULTA_PASSWORD=1234
DB_CONSULTA_CHARSET=utf8
```

---

## 🗄️ Conexiones de Base de Datos

### **Conexión Principal**
```php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD', '1234'),
    'charset' => 'SQL_ASCII',
    'collate' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => env('DB_SCHEMA', 'suc_app') . ',informes_app',
    'sslmode' => 'prefer',
],
```

### **Conexiones de Testing y Desarrollo**
```php
// Ejemplo para pgsql-desa
'pgsql-desa' => [
    'driver' => 'pgsql',
    'host' => env('DB_TEST_HOST', '127.0.0.1'),
    'port' => env('DB_TEST_PORT', '5434'),
    'database' => env('DB_TEST_DATABASE', 'desa'),
    'username' => env('DB_TEST_USERNAME', 'postgres'),
    'password' => env('DB_TEST_PASSWORD', '1234'),
    'charset' => 'SQL_ASCII',
    'collate' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'mapuche,suc',
    'sslmode' => 'prefer',
],
```

### **Conexiones de Backup Post-Producción**
```php
// Ejemplo para pgsql-2507
'pgsql-2507' => [
    'driver' => 'pgsql',
    'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
    'port' => env('DB_TEST_R2_PORT', '5435'),
    'database' => '2507',
    'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
    'password' => env('DB_TEST_R2_PASSWORD', '1234'),
    'charset' => 'SQL_ASCII',
    'collate' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'mapuche,suc',
    'sslmode' => 'prefer',
],
```

---

## 🔧 Configuración Técnica

### **Parámetros de Conexión**

| Parámetro | Descripción | Valor por Defecto |
|-----------|-------------|-------------------|
| `driver` | Driver de base de datos | `pgsql` |
| `host` | Host del servidor | `127.0.0.1` |
| `port` | Puerto del servidor | `5432` |
| `database` | Nombre de la base de datos | Variable por entorno |
| `username` | Usuario de conexión | `postgres` |
| `password` | Contraseña de conexión | `1234` |
| `charset` | Charset de la conexión | `SQL_ASCII` |
| `collate` | Collation de la conexión | `utf8` |
| `prefix` | Prefijo de tablas | `''` |
| `prefix_indexes` | Prefijo en índices | `true` |
| `search_path` | Schema de búsqueda | Variable por conexión |
| `sslmode` | Modo SSL | `prefer` |

### **Configuración de Encoding**

```php
'charset' => 'SQL_ASCII',
'collate' => 'utf8',
```

**¿Por qué esta configuración?**
- **`SQL_ASCII`**: Compatible con bases de datos existentes
- **`utf8` collate**: Permite manejo correcto de caracteres especiales
- **Consistencia**: Todas las conexiones usan la misma configuración

---

## 📖 Guías de Uso

### **1. Usar una Conexión Específica**

```php
// En un modelo
class Usuario extends Model
{
    protected $connection = 'pgsql-desa';
}

// En una consulta
DB::connection('pgsql-prod')->table('usuarios')->get();

// En una transacción
DB::connection('pgsql-test')->transaction(function () {
    // Operaciones en la transacción
});
```

### **2. Cambiar Conexión por Defecto**

```php
// En config/database.php
'default' => env('DB_CONNECTION', 'pgsql-desa'),

// O dinámicamente
DB::purge('default');
Config::set('database.default', 'pgsql-prod');
```

### **3. Verificar Conexiones**

```php
// Verificar si una conexión está activa
if (DB::connection('pgsql-prod')->getPdo()) {
    echo "Conexión activa";
}

// Obtener información de la conexión
$connection = DB::connection('pgsql-test');
$database = $connection->getDatabaseName();
$host = $connection->getConfig('host');
```

### **4. Migraciones en Conexiones Específicas**

```bash
# Ejecutar migraciones en una conexión específica
php artisan migrate --database=pgsql-desa

# Ejecutar seeders en una conexión específica
php artisan db:seed --database=pgsql-test
```

### **5. Configuración por Entorno**

```php
// En AppServiceProvider
public function boot()
{
    if (app()->environment('local')) {
        Config::set('database.default', 'pgsql-local');
    } elseif (app()->environment('testing')) {
        Config::set('database.default', 'pgsql-test');
    } elseif (app()->environment('production')) {
        Config::set('database.default', 'pgsql-prod');
    }
}
```

---

## 🔍 Troubleshooting

### **Problemas Comunes**

#### **1. Error de Conexión**
```
SQLSTATE[08006] [7] could not connect to server
```

**Solución:**
- Verificar que el servidor PostgreSQL esté ejecutándose
- Confirmar host, puerto y credenciales en `.env`
- Verificar firewall y configuración de red

#### **2. Error de Encoding**
```
SQLSTATE[22021] [7] character with byte sequence 0xXX in encoding "SQL_ASCII" has no equivalent in encoding "UTF8"
```

**Solución:**
- Verificar configuración de `charset` y `collate`
- Asegurar que la base de datos use `SQL_ASCII`
- Revisar datos que contengan caracteres especiales

#### **3. Error de Schema**
```
SQLSTATE[42P01] [7] relation "table_name" does not exist
```

**Solución:**
- Verificar `search_path` en la configuración
- Confirmar que el schema existe en la base de datos
- Ejecutar migraciones en la conexión correcta

#### **4. Error de Permisos**
```
SQLSTATE[42501] [7] permission denied for table table_name
```

**Solución:**
- Verificar permisos del usuario en la base de datos
- Confirmar que el usuario tenga acceso al schema
- Revisar configuración de roles en PostgreSQL

### **Comandos de Diagnóstico**

```bash
# Verificar conexiones activas
php artisan tinker
>>> DB::connection('pgsql-prod')->getPdo();

# Verificar configuración
php artisan config:show database

# Probar conexión
php artisan db:show --database=pgsql-test

# Ver logs de base de datos
tail -f storage/logs/laravel.log | grep -i database
```

### **Logs de Debug**

```php
// Habilitar logs de consultas SQL
DB::connection('pgsql-prod')->enableQueryLog();
// ... ejecutar consultas ...
$queries = DB::connection('pgsql-prod')->getQueryLog();
Log::info('SQL Queries:', $queries);
```

---

## 📝 Notas de Mantenimiento

### **Agregar Nueva Conexión**

1. **Agregar variables en `.env.example`:**
```env
DB_NEW_HOST=127.0.0.1
DB_NEW_PORT=5432
DB_NEW_DATABASE=new_db
DB_NEW_USERNAME=postgres
DB_NEW_PASSWORD=1234
DB_NEW_CHARSET=utf8
```

2. **Agregar conexión en `config/database.php`:**
```php
'pgsql-new' => [
    'driver' => 'pgsql',
    'host' => env('DB_NEW_HOST', '127.0.0.1'),
    'port' => env('DB_NEW_PORT', '5432'),
    'database' => env('DB_NEW_DATABASE', 'new_db'),
    'username' => env('DB_NEW_USERNAME', 'postgres'),
    'password' => env('DB_NEW_PASSWORD', '1234'),
    'charset' => 'SQL_ASCII',
    'collate' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
],
```

### **Backup y Restore**

```bash
# Backup de una base de datos específica
pg_dump -h 127.0.0.1 -p 5434 -U postgres -d desa > backup_desa.sql

# Restore de una base de datos
psql -h 127.0.0.1 -p 5434 -U postgres -d desa < backup_desa.sql
```

---

## 📞 Soporte

Para problemas específicos con la configuración de bases de datos:

1. **Revisar logs**: `storage/logs/laravel.log`
2. **Verificar configuración**: `php artisan config:show database`
3. **Probar conexiones**: Usar comandos de diagnóstico
4. **Documentar cambios**: Actualizar esta documentación

---

*Última actualización: Diciembre 2024*
*Versión de Laravel: 11.x*
*Base de datos: PostgreSQL*