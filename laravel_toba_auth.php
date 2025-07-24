<?php

// 1. CONFIGURACIÓN DE BASE DE DATOS (config/database.php)
/*
'toba' => [
    'driver' => 'pgsql', // o mysql según tu BD
    'host' => env('TOBA_DB_HOST', 'localhost'),
    'port' => env('TOBA_DB_PORT', '5432'),
    'database' => env('TOBA_DB_DATABASE'),
    'username' => env('TOBA_DB_USERNAME'),
    'password' => env('TOBA_DB_PASSWORD'),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
],
*/

// 2. MODELO PARA USUARIO DE TOBA (app/Models/TobaUser.php)
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TobaUser extends Authenticatable
{
    protected $connection = 'toba';
    protected $table = 'apex_usuario';
    protected $primaryKey = 'usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Toba no maneja created_at/updated_at
    
    protected $fillable = [
        'usuario', 
        'nombre', 
        'email',
        'autentificacion',
        'bloqueado',
        'parametro_a',
        'parametro_b', 
        'parametro_c',
        'forzar_cambio_pwd',
        'requiere_segundo_factor'
    ];

    protected $hidden = [
        'clave'
    ];

    protected $casts = [
        'bloqueado' => 'boolean',
        'forzar_cambio_pwd' => 'boolean',
        'requiere_segundo_factor' => 'boolean',
        'vencimiento' => 'date',
        'hora_entrada' => 'datetime:H:i:s',
        'hora_salida' => 'datetime:H:i:s'
    ];

    // Campos que Laravel espera para autenticación
    public function getAuthIdentifierName()
    {
        return 'usuario';
    }

    public function getAuthIdentifier()
    {
        return $this->getAttribute('usuario');
    }

    public function getAuthPassword()
    {
        return $this->getAttribute('clave');
    }

    // Métodos de utilidad basados en los campos de Toba
    public function estaBloqueado()
    {
        return $this->bloqueado == 1;
    }

    public function requiereSegundoFactor()
    {
        return $this->requiere_segundo_factor == 1;
    }

    public function debeForzarCambioPwd()
    {
        return $this->forzar_cambio_pwd == 1;
    }

    public function getParametro($parametro)
    {
        $parametro = strtolower(trim($parametro));
        if (!in_array($parametro, ['a', 'b', 'c'])) {
            throw new \InvalidArgumentException("Parámetro '$parametro' es inválido. Debe ser 'a', 'b' o 'c'.");
        }
        
        $campo = 'parametro_' . $parametro;
        return $this->getAttribute($campo);
    }

    // Scope para usuarios activos (no bloqueados)
    public function scopeActivos($query)
    {
        return $query->where('bloqueado', 0);
    }

    // Scope para usuarios que no requieren cambio de contraseña
    public function scopeSinForzarCambio($query)
    {
        return $query->where('forzar_cambio_pwd', 0);
    }
}

// 3. IMPLEMENTACIÓN COMPLETA DE TOBA_HASH (app/Services/TobaHashAdapter.php)
/**
 * Implementación completa de la clase toba_hash para Laravel
 * Replica exactamente la funcionalidad de Toba
 */
class TobaHashAdapter
{
    protected $rounds = 10;
    protected $metodo = 'bcrypt';
    private $randomState = null;
    private $indicadores_hash = array('$5

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Verificar si el usuario está bloqueado
            if ($datos_usuario->bloqueado == 1) {
                Log::error("El usuario '$id_usuario' está bloqueado");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario->autentificacion ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario->clave);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario->clave, $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario desde apex_usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario')
            ->select([
                'usuario', 
                'clave', 
                'autentificacion', 
                'nombre', 
                'email',
                'bloqueado',
                'forzar_cambio_pwd',
                'requiere_segundo_factor'
            ])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación exacta de la función encriptar_con_sal de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . ($resultado ?? ''));
    }

    /**
     * Genera un salt aleatorio (método simple para fallback)
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        // Verificar si el usuario existe y no está bloqueado antes de intentar login
        $tobaUser = TobaUser::where('usuario', $credentials['usuario'])->first();
        
        if (!$tobaUser) {
            return back()->withErrors([
                'usuario' => 'El usuario no existe.',
            ]);
        }

        if ($tobaUser->estaBloqueado()) {
            return back()->withErrors([
                'usuario' => 'El usuario está bloqueado.',
            ]);
        }

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            
            // Verificar si requiere cambio de contraseña
            if ($tobaUser->debeForzarCambioPwd()) {
                return redirect()->route('password.change')
                    ->with('warning', 'Debe cambiar su contraseña antes de continuar.');
            }
            
            // Verificar si requiere segundo factor
            if ($tobaUser->requiereSegundoFactor()) {
                return redirect()->route('two-factor.verify')
                    ->with('info', 'Ingrese su segundo factor de autenticación.');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/, '$6

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/, '$1

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/);
    private $indicadores_bcrypt = array('$2y

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/,'$2a

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/, '$2x

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/);

    public function __construct($metodo = null)
    {
        if (version_compare(PHP_VERSION, '5.3.2') < 0) {
            throw new \Exception('Se requiere PHP 5.3.2 al menos para usar esta clase');
        }

        if (!is_null($metodo)) {
            $this->metodo = $metodo;
        }
    }

    public function setCiclos($nro)
    {
        $this->rounds = ($nro > 10) ? $nro : 10;
    }

    public function hash($input)
    {
        $hash = crypt($input, $this->getSalt());
        if (strlen($hash) > 13) {
            return $hash;
        }
        throw new \Exception('Se produjo un error al crear el hash');
    }

    public function getHashVerificador($input, $existingHash)
    {
        return crypt($input, $existingHash);
    }

    public function verify($input, $existingHash)
    {
        $hash = crypt($input, $existingHash);
        return hash_equals($hash, $existingHash);
    }

    private function getSalt()
    {
        switch (strtoupper($this->metodo)) {
            case 'BCRYPT':
                $str_inicial = (version_compare(PHP_VERSION, '5.3.7') < 0) ? '$2a

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/ : '$2y

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/;
                $salt = sprintf($str_inicial . '%02d

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/, $this->rounds);
                break;

            case 'SHA512':
                $vueltas = ($this->rounds < 1000) ? $this->rounds * 1000 : $this->rounds + 5000;
                $salt = sprintf('$6$rounds=%d

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/, $vueltas);
                break;

            case 'SHA256':
                $vueltas = ($this->rounds < 1000) ? $this->rounds * 1000 : $this->rounds + 5000;
                $salt = sprintf('$5$rounds=%d

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/, $vueltas);
                break;

            case 'MD5':
                $salt = '$1

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/;
                break;

            default:
                \Log::debug("Se suministró un algoritmo no esperado para el hash: {$this->metodo}");
                $salt = '';
        }

        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            try {
                $bytes = random_bytes(16); // PHP7.0
            } catch (\Exception $e) {
                $bytes = $this->getRandomBytes(16); // Old way
            }
        } else {
            $bytes = $this->getRandomBytes(16); // Old way
        }
        
        $salt .= $this->encodeBytes($bytes);
        return $salt;
    }

    private function getRandomBytes($count)
    {
        $bytes = '';
        if (function_exists('openssl_random_pseudo_bytes') && (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) {
            try {
                $bytes = openssl_random_pseudo_bytes($count);
            } catch (\Exception $e) {
                $bytes = '';
            }
        }
        
        if ($bytes === '' && is_readable('/dev/urandom') && ($hRand = @fopen('/dev/urandom', 'rb')) !== FALSE) {
            $bytes = fread($hRand, $count);
            if (false === $bytes) {
                $bytes = '';
            }
            fclose($hRand);
        }

        if (strlen($bytes) < $count) {
            $bytes = '';
            if ($this->randomState === null) {
                $this->randomState = microtime();
                if (function_exists('getmypid')) {
                    $this->randomState .= getmypid();
                }
            }

            for ($i = 0; $i < $count; $i += 16) {
                $this->randomState = md5(microtime() . $this->randomState);
                if (PHP_VERSION >= '5') {
                    $bytes .= md5($this->randomState, true);
                } else {
                    $bytes .= pack('H*', md5($this->randomState));
                }
            }

            $bytes = substr($bytes, 0, $count);
        }
        return $bytes;
    }

    private function encodeBytes($input)
    {
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $output = '';
        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);
        return $output;
    }
}

// 4. SERVICIO DE AUTENTICACIÓN TOBA (app/Services/TobaAuthService.php)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario['autentificacion'] ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario['clave']);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario['clave'], $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario') // Ajusta el nombre de tu tabla
            ->select(['usuario', 'clave', 'autentificacion', 'nombre', 'email'])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación de la función encriptar_con_sal de Toba
     * Basada en la función original de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        // Para verificación de passwords existentes, $sal será la clave almacenada
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . $resultado);
    }

    /**
     * Genera un salt aleatorio
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}

// 4. PROVIDER DE AUTENTICACIÓN PERSONALIZADO (app/Auth/TobaUserProvider.php)
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        return TobaUser::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['usuario'])) {
            return null;
        }

        return TobaUser::where('usuario', $credentials['usuario'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
        return false;
    }
}

// 5. GUARD PERSONALIZADO (app/Auth/TobaGuard.php)
use Illuminate\Auth\SessionGuard;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        return parent::attempt($credentials, $remember);
    }
}

// 6. CONFIGURACIÓN EN AuthServiceProvider (app/Providers/AuthServiceProvider.php)
/*
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
*/

// 7. CONFIGURACIÓN EN config/auth.php
/*
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
*/

// 8. CONTROLADOR DE LOGIN (app/Http/Controllers/Auth/TobaLoginController.php)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// 9. VISTA DE LOGIN (resources/views/auth/toba-login.blade.php)
/*
<form method="POST" action="{{ route('toba.login') }}">
    @csrf
    <div>
        <label for="usuario">Usuario:</label>
        <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
        @error('usuario')
            <span>{{ $message }}</span>
        @enderror
    </div>
    
    <div>
        <label for="clave">Contraseña:</label>
        <input id="clave" type="password" name="clave" required>
    </div>
    
    <button type="submit">Iniciar Sesión</button>
</form>
*/

// 10. RUTAS (routes/web.php)
/*
Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
Route::post('/logout', [TobaLoginController::class, 'logout'])->name('logout');
*/