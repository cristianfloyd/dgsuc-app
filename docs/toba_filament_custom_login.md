# Implementación de Login Toba con Estilos FilamentPHP

Este documento detalla cómo crear una página de login profesional para el sistema de autenticación Toba utilizando componentes y estilos de FilamentPHP 3.x.

## Resumen del Sistema Actual

La aplicación cuenta con un sistema de autenticación dual:
- **Laravel Eloquent**: Guard `web` para usuarios de Laravel (`/login`)
- **Toba**: Guard `toba` para usuarios del sistema Toba (`/toba/login`)

### Componentes Existentes

- **TobaGuard**: Implementa la lógica de autenticación personalizada
- **TobaUserProvider**: Maneja la sincronización entre usuarios Toba y Laravel
- **TobaLoginController**: Controlador que autentica en ambos guards simultáneamente
- **Vista actual**: `resources/views/auth/toba-login.blade.php` (básica sin estilos)

## Estrategia de Implementación

### 1. Creación de Página de Login Personalizada con FilamentPHP

Basándose en la documentación oficial de FilamentPHP, implementaremos una página de login que:

- Extienda las clases base de autenticación de Filament
- Utilice componentes de formulario de Filament para consistencia visual
- Mantenga la compatibilidad con el sistema Toba existente
- Implemente el flujo de autenticación dual (toba + web guards)

### 2. Estructura de Archivos Propuesta

```
app/Filament/Auth/
├── TobaLogin.php                    # Página de login personalizada
└── TobaLoginController.php          # Controlador específico para Filament

resources/views/filament/auth/
└── toba-login.blade.php             # Vista Blade con componentes Filament
```

### 3. Implementación Detallada

#### A. Página de Login Personalizada (`app/Filament/Auth/TobaLogin.php`)

```php
<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TobaLogin extends BaseLogin
{
    protected static string $view = 'filament.auth.toba-login';
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('usuario')
                    ->label('Usuario')
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),
                    
                TextInput::make('clave')
                    ->label('Contraseña')
                    ->password()
                    ->required()
                    ->extraInputAttributes(['tabindex' => 2]),
            ])
            ->statePath('data');
    }
    
    public function authenticate(): ?string
    {
        $data = $this->form->getState();
        
        $credentials = [
            'usuario' => $data['usuario'],
            'clave' => $data['clave'],
        ];
        
        // Intentar autenticación con guard Toba
        if (Auth::guard('toba')->attempt($credentials)) {
            $tobaUser = Auth::guard('toba')->user();
            
            // Sincronizar con guard web
            Auth::guard('web')->login($tobaUser);
            
            session()->regenerate();
            
            return '/'; // Redirección exitosa
        }
        
        throw ValidationException::withMessages([
            'data.usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }
    
    protected function getHeading(): string
    {
        return 'Iniciar Sesión - Sistema Toba';
    }
    
    protected function getSubheading(): ?string
    {
        return 'Accede con tus credenciales del sistema Toba';
    }
}
```

#### B. Vista Blade Personalizada (`resources/views/filament/auth/toba-login.blade.php`)

```blade
<x-filament-panels::page.simple>
    @if (filament()->hasLogin())
        <x-slot name="subheading">
            {{ $this->getSubheading() }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.before') }}

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.after') }}

    <div class="text-center">
        <x-filament::link
            href="{{ route('login') }}"
            size="sm"
        >
            ¿Prefieres usar tu cuenta Laravel?
        </x-filament::link>
    </div>
</x-filament-panels::page.simple>
```

#### C. Actualización del Controlador (`app/Http/Controllers/Auth/TobaLoginController.php`)

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Filament\Auth\TobaLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        // Renderizar la página de Filament
        return app(TobaLogin::class)->render();
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);
        
        $credentials = $request->only('usuario', 'clave');
        
        if (Auth::guard('toba')->attempt($credentials)) {
            $tobaUser = Auth::guard('toba')->user();
            Auth::guard('web')->login($tobaUser);
            
            $request->session()->regenerate();
            
            return redirect()->intended('/');
        }
        
        throw ValidationException::withMessages([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }
    
    public function logout(Request $request)
    {
        Auth::guard('toba')->logout();
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/toba/login');
    }
}
```

### 4. Configuración de Rutas

Actualizar `routes/web.php` para usar la nueva implementación:

```php
// Rutas de autenticación Toba con prefijo
Route::prefix('toba')->group(function () {
    Route::get('/login', [TobaLoginController::class, 'showLoginForm'])
        ->name('toba.login.form');
    Route::post('/login', [TobaLoginController::class, 'login'])
        ->name('toba.login');
    Route::post('/logout', [TobaLoginController::class, 'logout'])
        ->name('toba.logout');
});
```

### 5. Características del Diseño

#### Componentes FilamentPHP Utilizados
- **TextInput**: Campos de usuario y contraseña con estilos consistentes
- **Form**: Contenedor de formulario con validación integrada
- **Page Layout**: Layout simple y profesional
- **Link Components**: Enlaces estilizados
- **Actions**: Botones con estilos uniformes

#### Elementos de UX/UI
- **Autofocus**: Campo usuario enfocado al cargar
- **Tabindex**: Navegación por teclado optimizada
- **Autocomplete**: Sugerencias del navegador habilitadas
- **Validación en tiempo real**: Errores mostrados inmediatamente
- **Mensajes localizados**: Textos en español
- **Responsive**: Adaptable a dispositivos móviles

### 6. Funcionalidades Avanzadas Opcionales

#### A. Tema Personalizado
```php
// En TobaLogin.php
protected function getViewData(): array
{
    return [
        'brandName' => config('app.name'),
        'brandLogo' => asset('images/logo-uba.png'),
    ];
}
```

#### B. Recordar Sesión
```php
// Agregar checkbox "Recordarme"
Checkbox::make('remember')
    ->label('Recordarme')
```

#### C. Redirección Inteligente
```php
// En authenticate()
$intended = session()->pull('url.intended', '/');
return $intended;
```

## Beneficios de la Implementación

1. **Consistencia Visual**: Usa el sistema de diseño de FilamentPHP
2. **Experiencia Profesional**: Login moderno y pulido
3. **Mantenibilidad**: Código organizado siguiendo patrones de Filament
4. **Accesibilidad**: Componentes accesibles por defecto
5. **Responsivo**: Funciona en todos los dispositivos
6. **Validación Robusta**: Sistema de validación integrado
7. **Compatibilidad**: Mantiene toda la funcionalidad Toba existente

## Consideraciones de Implementación

### Dependencias
- FilamentPHP 3.2+ (ya instalado según composer.json)
- Laravel 11 (ya configurado)
- Sistema Toba existente (ya implementado)

### Archivos a Modificar
1. Crear: `app/Filament/Auth/TobaLogin.php`
2. Crear: `resources/views/filament/auth/toba-login.blade.php`
3. Actualizar: `app/Http/Controllers/Auth/TobaLoginController.php`
4. Opcional: Actualizar estilos CSS personalizados

### Testing
- Verificar autenticación dual funciona correctamente
- Validar estilos en diferentes navegadores
- Probar responsividad en dispositivos móviles
- Confirmar accesibilidad con lectores de pantalla

## Conclusión

Esta implementación combina la robustez del sistema de autenticación Toba existente con la elegancia y profesionalismo de FilamentPHP, creando una experiencia de usuario superior sin comprometer la funcionalidad técnica.