# Guía de Integración Laravel-Toba Authentication

Esta guía te permitirá integrar la autenticación de usuarios de Toba en una aplicación Laravel 11, permitiendo que los usuarios se autentiquen usando sus credenciales existentes en Toba.

## 📋 Prerrequisitos

- Laravel 11 instalado
- Acceso a la base de datos de Toba
- PHP >= 8.1
- Extensión PostgreSQL habilitada (si Toba usa PostgreSQL)

## 🚀 Pasos de Implementación

### 1. Configuración de Base de Datos

#### 1.1 Configurar variables de entorno
Agregar las siguientes variables al archivo `.env`:

```env
# Configuración de base de datos Toba
TOBA_DB_HOST=localhost
TOBA_DB_PORT=5432
TOBA_DB_DATABASE=tu_bd_toba
TOBA_DB_USERNAME=tu_usuario_toba
TOBA_DB_PASSWORD=tu_password_toba
```

#### 1.2 Configurar conexión en config/database.php
Agregar la siguiente configuración en el array `connections`:

```php
'toba' => [
    'driver' => 'pgsql',
    'host' => env('TOBA_DB_HOST'),
    'port' => env('TOBA_DB_PORT'),
    'database' => env('TOBA_DB_DATABASE'),
    'username' => env('TOBA_DB_USERNAME'),
    'password' => env('TOBA_DB_PASSWORD'),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
],
```

### 2. Creación de Archivos

#### 2.1 Crear servicios de autenticación

```bash
# Crear adaptador para toba_hash
php artisan make:class Services/TobaHashAdapter

# Crear servicio de autenticación Toba
php artisan make:class Services/TobaAuthService
```

#### 2.2 Crear modelo de usuario

```bash
# Crear modelo para usuarios de Toba
php artisan make:model TobaUser
```

#### 2.3 Crear providers y guards de autenticación

```bash
# Crear provider personalizado
php artisan make:class Auth/TobaUserProvider

# Crear guard personalizado
php artisan make:class Auth/TobaGuard
```

#### 2.4 Crear controlador de autenticación

```bash
# Crear controlador de login
php artisan make:controller Auth/TobaLoginController
```

#### 2.5 Crear vista de login

```bash
# Crear vista de login
php artisan make:view auth.toba-login
```

#### 2.6 Crear middleware (opcional)

```bash
# Crear middleware para validaciones adicionales
php artisan make:middleware TobaAuthMiddleware
```

### 3. Configuración de Autenticación

#### 3.1 Registrar providers en AuthServiceProvider

Modificar `app/Providers/AuthServiceProvider.php` y agregar en el método `boot()`:

```php
public function boot()
{
    $this->registerPolicies();

    // Registrar el provider personalizado
    Auth::provider('toba', function ($app, array $config) {
        return new TobaUserProvider($app->make(TobaAuthService::class));
    });

    // Registrar el guard personalizado
    Auth::extend('toba', function ($app, $name, array $config) {
        return new TobaGuard(
            $name,
            Auth::createUserProvider($config['provider']),
            $app['session.store']
        );
    });
}
```

#### 3.2 Configurar guards y providers

Modificar `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'toba',
        'provider' => 'toba_users',
    ],
    // ... otros guards
],

'providers' => [
    'toba_users' => [
        'driver' => 'toba',
        'model' => App\Models\TobaUser::class,
    ],
    // ... otros providers
],
```

### 4. Configuración de Rutas

#### 4.1 Agregar rutas de autenticación

En `routes/web.php`:

```php
use App\Http\Controllers\Auth\TobaLoginController;

// Rutas de autenticación Toba
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');

// Rutas adicionales (opcional)
Route::get('/password/change', [TobaLoginController::class, 'showChangePasswordForm'])->name('password.change');
Route::get('/two-factor/verify', [TobaLoginController::class, 'showTwoFactorForm'])->name('two-factor.verify');
```

### 5. Archivos a Implementar

Después de ejecutar los comandos anteriores, deberás implementar el código en los siguientes archivos:

- ✅ `app/Services/TobaHashAdapter.php` - Implementación de toba_hash
- ✅ `app/Services/TobaAuthService.php` - Lógica de autenticación
- ✅ `app/Models/TobaUser.php` - Modelo de usuario Toba
- ✅ `app/Auth/TobaUserProvider.php` - Provider de autenticación
- ✅ `app/Auth/TobaGuard.php` - Guard personalizado
- ✅ `app/Http/Controllers/Auth/TobaLoginController.php` - Controlador de login
- ✅ `resources/views/auth/toba-login.blade.php` - Vista de login

### 6. Testing y Verificación

#### 6.1 Verificar conexión a base de datos

```bash
# Verificar que Laravel puede conectarse a Toba
php artisan tinker
```

En tinker:
```php
DB::connection('toba')->table('apex_usuario')->count();
```

#### 6.2 Probar autenticación

```bash
# Verificar que el servicio funciona
php artisan tinker
```

En tinker:
```php
$service = app(App\Services\TobaAuthService::class);
$result = $service->autenticar('usuario_prueba', 'contraseña_prueba');
var_dump($result);
```

### 7. Comandos Adicionales (Opcional)

#### 7.1 Para desarrollo y testing

```bash
# Crear tests unitarios
php artisan make:test TobaAuthTest --unit
php artisan make:test TobaLoginTest

# Crear request de validación personalizado
php artisan make:request TobaLoginRequest

# Crear seeder para datos de prueba
php artisan make:seeder TobaUserSeeder
```

#### 7.2 Para migración de datos (si es necesario)

```bash
# Crear migración para tabla auxiliar
php artisan make:migration create_toba_sync_table

# Crear job para sincronización
php artisan make:job SyncTobaUsers
```

### 8. Consideraciones de Seguridad

- ✅ **Validación de usuarios bloqueados**
- ✅ **Soporte para todos los algoritmos de hash de Toba**
- ✅ **Manejo de segundo factor de autenticación**
- ✅ **Detección de cambio forzado de contraseña**
- ✅ **Protección contra timing attacks con `hash_equals()`**

### 9. Funcionalidades Implementadas

- ✅ **Autenticación con credenciales de Toba**
- ✅ **Soporte para algoritmos**: plano, md5, sha1, sha256, sha512, bcrypt
- ✅ **Validación de usuarios bloqueados**
- ✅ **Manejo de parámetros A, B, C de usuario**
- ✅ **Soporte para segundo factor**
- ✅ **Detección de cambio forzado de contraseña**

### 10. Troubleshooting

#### Problemas comunes:

1. **Error de conexión a BD:**
   - Verificar credenciales en `.env`
   - Confirmar que PostgreSQL está instalado y configurado

2. **Usuario no encontrado:**
   - Verificar nombre de tabla `apex_usuario`
   - Confirmar que el usuario existe en Toba

3. **Autenticación fallida:**
   - Revisar logs en `storage/logs/laravel.log`
   - Verificar algoritmo de hash en BD

### 11. Logs y Debugging

Para habilitar logs detallados, agregar en `.env`:

```env
LOG_LEVEL=debug
APP_DEBUG=true
```

Los logs de autenticación se guardarán automáticamente y mostrarán:
- Intentos de login fallidos
- Usuarios bloqueados
- Errores de conexión

---

## ✅ Checklist de Implementación

- [ ] Configurar variables de entorno
- [ ] Configurar conexión de BD en `config/database.php`
- [ ] Ejecutar comandos artisan para crear archivos
- [ ] Implementar código en cada archivo creado
- [ ] Modificar `AuthServiceProvider.php`
- [ ] Actualizar `config/auth.php`
- [ ] Configurar rutas en `web.php`
- [ ] Probar conexión a BD de Toba
- [ ] Verificar autenticación con usuario de prueba
- [ ] Revisar logs para errores

Una vez completados todos estos pasos, tendrás una integración completa entre Laravel y Toba que permitirá a los usuarios autenticarse usando sus credenciales existentes en Toba.