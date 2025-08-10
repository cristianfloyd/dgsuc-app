# Guía de Integración Laravel-Toba Authentication

Esta guía te permitirá integrar la autenticación de usuarios de Toba en una aplicación Laravel 11, manteniendo compatibilidad con la autenticación de Laravel existente y permitiendo que los usuarios se autentiquen usando sus credenciales de Toba.

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

### 2. Preparación de Base de Datos Laravel

#### 2.1 Migración para columna toba_usuario

```bash
# Crear migración para agregar campo toba_usuario a tabla users
php artisan make:migration add_toba_usuario_to_users_table
```

Implementar la migración:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('toba_usuario')->nullable()->unique()->after('email');
        $table->index('toba_usuario');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropIndex(['toba_usuario']);
        $table->dropColumn('toba_usuario');
    });
}
```

```bash
# Ejecutar migración
php artisan migrate
```

### 3. Creación de Archivos

#### 3.1 Crear servicios de autenticación

```bash
# Crear adaptador para toba_hash
php artisan make:class Services/TobaHashAdapter

# Crear servicio de autenticación Toba
php artisan make:class Services/TobaAuthService
```

#### 3.2 Crear modelo de usuario

```bash
# Crear modelo para usuarios de Toba
php artisan make:model TobaUser
```

#### 3.3 Crear providers y guards de autenticación

```bash
# Crear provider personalizado
php artisan make:class Auth/TobaUserProvider

# Crear guard personalizado
php artisan make:class Auth/TobaGuard
```

#### 3.4 Crear controlador de autenticación

```bash
# Crear controlador de login
php artisan make:controller Auth/TobaLoginController
```

#### 3.5 Crear vista de login

```bash
# Crear vista de login
php artisan make:view auth.toba-login
```

#### 3.6 Crear middleware (opcional)

```bash
# Crear middleware para validaciones adicionales
php artisan make:middleware TobaAuthMiddleware
```

### 4. Configuración de Autenticación

#### 4.1 Registrar providers en AuthServiceProvider

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

#### 4.2 Configurar guards y providers

Modificar `config/auth.php` para mantener compatibilidad con ambos sistemas:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users', // Mantener guard por defecto
    ],
    'toba' => [
        'driver' => 'toba',
        'provider' => 'toba_users',
    ],
    // ... otros guards
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class, // Guard por defecto usa User
    ],
    'toba_users' => [
        'driver' => 'toba',
        'model' => App\Models\User::class, // Toba también usa User (sincronizado)
    ],
    // ... otros providers
],
```

### 5. Configuración de Rutas

#### 5.1 Agregar rutas de autenticación

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

### 6. Archivos a Implementar

Después de ejecutar los comandos anteriores, deberás implementar el código en los siguientes archivos:

- ✅ `app/Services/TobaHashAdapter.php` - Implementación de toba_hash
- ✅ `app/Services/TobaAuthService.php` - Lógica de autenticación
- ✅ `app/Models/TobaUser.php` - Modelo de usuario Toba (para conexión a BD Toba)
- ✅ `app/Auth/TobaUserProvider.php` - Provider que sincroniza Toba con Users de Laravel
- ✅ `app/Auth/TobaGuard.php` - Guard personalizado con sincronización
- ✅ `app/Http/Controllers/Auth/TobaLoginController.php` - Controlador con dual-guard login
- ✅ `resources/views/auth/toba-login.blade.php` - Vista de login

## 🔧 Arquitectura de la Integración

### Sistema Dual de Autenticación

La integración funciona manteniendo **dos sistemas de autenticación simultáneos**:

1. **Laravel Eloquent** (Guard 'web'): Autenticación tradicional con tabla `users`
2. **Toba Integration** (Guard 'toba'): Autenticación contra Toba con sincronización

### Flujo de Autenticación Toba

```mermaid
graph TD
    A[Usuario ingresa credenciales] --> B[TobaLoginController]
    B --> C[Auth::guard('toba')->attempt()]
    C --> D[TobaUserProvider->retrieveByCredentials()]
    D --> E{¿Usuario existe en BD Toba?}
    E -->|No| F[Return null - Login fallido]
    E -->|Sí| G[¿Usuario existe en Laravel users?]
    G -->|No| H[Crear nuevo usuario en users]
    G -->|Sí| I[Actualizar usuario existente]
    H --> J[User con ID numérico]
    I --> J
    J --> K[TobaUserProvider->validateCredentials()]
    K --> L[TobaAuthService->autenticar()]
    L --> M{¿Credenciales válidas?}
    M -->|No| N[Return false - Login fallido]
    M -->|Sí| O[Return User model]
    O --> P[Guard 'toba' autentica usuario]
    P --> Q[Sincronizar con Guard 'web']
    Q --> R[DatabaseSessionHandler guarda user_id numérico]
    R --> S[Login exitoso - Ambos guards activos]
```

### Sincronización de Usuarios

```php
// TobaUserProvider lógica de sincronización
1. Buscar por toba_usuario (ya sincronizado)
2. Si no existe, buscar por name o username (usuario Laravel existente)  
3. Si existe Laravel user: actualizar con toba_usuario
4. Si no existe: crear nuevo user con name = username = toba_usuario
5. Devolver User model con ID numérico para compatibilidad con sessions
```

### Compatibilidad con Sessions

```php
// TobaLoginController sincronización de guards
Auth::guard('toba')->attempt($credentials);        // Autentica con Toba
$tobaUser = Auth::guard('toba')->user();           // Usuario autenticado
Auth::guard('web')->login($tobaUser);              // Sincroniza con guard por defecto
// DatabaseSessionHandler usa Auth::user() (guard web) -> user_id numérico
```

### 7. Testing y Verificación

#### 7.1 Verificar conexión a base de datos

```bash
# Verificar que Laravel puede conectarse a Toba
php artisan tinker
```

En tinker:
```php
DB::connection('toba')->table('apex_usuario')->count();
```

#### 7.2 Probar autenticación

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

#### 7.3 Verificar sincronización

```bash
# Verificar usuarios sincronizados
php artisan tinker
```

En tinker:
```php
// Verificar usuarios con toba_usuario
use App\Models\User;
User::whereNotNull('toba_usuario')->get(['id', 'name', 'username', 'toba_usuario']);

// Verificar que guard toba funciona
Auth::guard('toba')->attempt(['usuario' => 'tu_usuario', 'clave' => 'tu_password']);
Auth::guard('toba')->user(); // Debe mostrar User model con ID numérico

// Verificar que guard web también funciona
Auth::user(); // Debe mostrar el mismo usuario
```

### 8. Comandos Adicionales (Opcional)

#### 8.1 Para desarrollo y testing

```bash
# Crear tests unitarios
php artisan make:test TobaAuthTest --unit
php artisan make:test TobaLoginTest

# Crear request de validación personalizado
php artisan make:request TobaLoginRequest

# Crear seeder para datos de prueba
php artisan make:seeder TobaUserSeeder
```

#### 8.2 Para migración de datos (si es necesario)

```bash
# Crear migración para tabla auxiliar
php artisan make:migration create_toba_sync_table

# Crear job para sincronización
php artisan make:job SyncTobaUsers
```

### 9. Consideraciones de Seguridad

- ✅ **Validación de usuarios bloqueados**
- ✅ **Soporte para todos los algoritmos de hash de Toba**
- ✅ **Manejo de segundo factor de autenticación**
- ✅ **Detección de cambio forzado de contraseña**
- ✅ **Protección contra timing attacks con `hash_equals()`**

### 10. Funcionalidades Implementadas

- ✅ **Autenticación dual**: Laravel Eloquent + Toba simultáneos
- ✅ **Sincronización automática**: Usuarios Toba → Laravel Users
- ✅ **Compatibilidad con sessions**: user_id numérico en base de datos
- ✅ **Sin duplicados**: Fusión inteligente de usuarios existentes
- ✅ **Autenticación con credenciales de Toba**
- ✅ **Soporte para algoritmos**: plano, md5, sha1, sha256, sha512, bcrypt
- ✅ **Validación de usuarios bloqueados**
- ✅ **Manejo de parámetros A, B, C de usuario**
- ✅ **Soporte para segundo factor**
- ✅ **Detección de cambio forzado de contraseña**

### 11. Troubleshooting

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

4. **user_id null en sessions:**
   - Verificar que TobaLoginController sincroniza guards
   - Confirmar que Auth::guard('web')->login($user) se ejecuta
   - Verificar que usuario tiene ID numérico válido

5. **Error "Unique violation" en username:**
   - Usuario ya existe en Laravel con mismo username
   - TobaUserProvider debe buscar por name OR username
   - Verificar que migración add_toba_usuario_to_users se ejecutó

6. **Guards no sincronizados:**
   - Auth::guard('toba')->user() funciona pero Auth::user() es null
   - Falta sincronización en TobaLoginController
   - Verificar que ambos guards usan mismo User model

### 12. Logs y Debugging

Para habilitar logs detallados, agregar en `.env`:

```env
LOG_LEVEL=debug
APP_DEBUG=true
```

Los logs de autenticación se guardarán automáticamente y mostrarán:
- Proceso completo de TobaUserProvider (retrieveByCredentials, validateCredentials)
- Sincronización de usuarios (creación, actualización, fusión)
- Estado de guards después del login (toba_user, default_user)
- Intentos de login fallidos
- Usuarios bloqueados
- Errores de conexión

#### Logs esperados en login exitoso:
```
TobaUserProvider retrieveByCredentials start {"usuario":"tu_usuario"}
TobaUserProvider: Toba user found {"usuario":"tu_usuario"}
Updated existing Laravel user with Toba data {"toba_usuario":"tu_usuario","laravel_user_id":1}
TobaGuard login exitoso {"user_id":1,"username":"id","user_class":"App\\Models\\User"}
Post-login state {"toba_user":1,"default_user":1,"session_id_before_check":"..."}
```

---

## ✅ Checklist de Implementación

### Configuración Inicial
- [ ] Configurar variables de entorno (.env)
- [ ] Configurar conexión de BD Toba en `config/database.php`
- [ ] Crear y ejecutar migración `add_toba_usuario_to_users_table`
- [ ] Ejecutar comandos artisan para crear archivos

### Implementación de Código
- [ ] Implementar `TobaHashAdapter.php`
- [ ] Implementar `TobaAuthService.php` 
- [ ] Implementar `TobaUser.php` (modelo para BD Toba)
- [ ] Implementar `TobaUserProvider.php` (con lógica de sincronización)
- [ ] Implementar `TobaGuard.php` 
- [ ] Implementar `TobaLoginController.php` (con dual-guard sync)
- [ ] Crear vista `toba-login.blade.php`

### Configuración de Autenticación
- [ ] Modificar `AuthServiceProvider.php` (registrar en boot())
- [ ] Actualizar `config/auth.php` (mantener web + agregar toba)
- [ ] Configurar rutas en `web.php`

### Testing y Verificación
- [ ] Probar conexión a BD de Toba
- [ ] Verificar que usuarios se sincronizan correctamente
- [ ] Confirmar que login funciona con ambos guards
- [ ] Verificar que sessions tienen user_id numérico (no null)
- [ ] Probar funcionalidades Toba (bloqueos, segundo factor, etc.)
- [ ] Revisar logs para confirmar flujo correcto

### Resultado Final
✅ **Sistema dual funcional**: Laravel Eloquent + Toba Authentication  
✅ **Sin duplicados**: Usuarios existentes se fusionan automáticamente  
✅ **Sessions compatibles**: user_id numérico en base de datos  
✅ **Funcionalidades Toba**: Todas las validaciones y características preservadas  

Una vez completados todos estos pasos, tendrás una integración completa entre Laravel y Toba que permite autenticación dual sin conflictos y con total compatibilidad.