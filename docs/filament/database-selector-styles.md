# Selector de Conexión de Base de Datos - Estilos Disponibles

Este documento describe las diferentes opciones de estilo disponibles para el selector de conexión de base de datos en el header de Filament.

## Opciones Disponibles

### 1. Estilo Principal (Actual)
**Archivo:** `resources/views/livewire/database-connection-selector.blade.php`
**Componente:** `DatabaseConnectionSelector`

Características:
- Diseño compacto con fondo gris claro
- Icono de base de datos
- Indicador visual de estado (punto verde)
- Ancho: 28 (w-28)
- Ideal para headers con espacio moderado

### 2. Estilo Minimalista
**Archivo:** `resources/views/livewire/database-connection-selector-minimal.blade.php`

Características:
- Diseño ultra compacto
- Sin icono
- Indicador de estado pequeño
- Ancho: 24 (w-24)
- Ideal para headers con espacio limitado

### 3. Estilo Badge (Recomendado)
**Archivo:** `resources/views/livewire/database-connection-selector-badge.blade.php`
**Componente:** `DatabaseConnectionSelectorBadge`

Características:
- Diseño tipo badge con hover effects
- Icono pequeño integrado
- Indicador de estado integrado
- Ancho: 20 (w-20)
- Transiciones suaves
- Ideal para headers modernos

## Configuración Global

El selector de conexión de base de datos está configurado globalmente en `app/Providers/FilamentServiceProvider.php` para que aparezca en todos los paneles de Filament.

```php
// En app/Providers/FilamentServiceProvider.php
FilamentView::registerRenderHook(
    PanelsRenderHook::TOPBAR_END,
    fn (): string => Blade::render('@livewire(\'database-connection-selector-badge\')')
);
```

## Cómo Cambiar el Estilo

### Opción 1: Cambiar el componente globalmente

### Opción 2: Usar la vista minimalista

```php
// En el componente Livewire
public function render()
{
    return view('livewire.database-connection-selector-minimal');
}
```

### Opción 3: Personalizar el estilo actual

Modifica directamente el archivo `resources/views/livewire/database-connection-selector.blade.php` según tus necesidades.

## Personalización de Colores

Los colores se definen en el componente Livewire:

```php
$colorClasses = [
    'pgsql-mapuche' => 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400',
    'pgsql-prod' => 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400',
    'pgsql-liqui' => 'bg-yellow-50 border-yellow-200 text-yellow-700 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-400',
];

// Altura personalizada para el select
'class' => 'text-xs font-medium h-8 ' . ($colorClasses[$state] ?? ''),
```

## Recomendaciones

1. **Para headers modernos:** Usar el estilo Badge
2. **Para headers compactos:** Usar el estilo Minimalista
3. **Para headers estándar:** Usar el estilo Principal

## Notas Técnicas

- Todos los estilos son responsivos
- Compatibles con modo oscuro
- Usan Tailwind CSS para consistencia con Filament
- Incluyen notificaciones Alpine.js
- Mantienen la funcionalidad completa del selector 