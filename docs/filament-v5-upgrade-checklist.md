# Checklist de actualización Filament v4 → v5

Guía paso a paso según la [guía oficial de Filament v5](https://filamentphp.com/docs/5.x/upgrade-guide) y la [guía de actualización de Livewire v4](https://livewire.laravel.com/docs/4.x/upgrading) (prerrequisito).

**Estado:** Pendiente. Marca cada ítem al completarlo.

> **Nota:** La aplicación corre en Docker. Todos los comandos siguientes usan el prefijo `docker compose exec app`.

---

## Objetivo

Dejar el proyecto estable en **Filament v5**, lo que implica:

1. **Prerrequisito:** Actualizar **Livewire a v4** (Filament v5 lo requiere).
2. **Fase principal:** Ejecutar el script de actualización de Filament y actualizar dependencias.
3. **Revisión manual:** Ajustar lo que el script no cubra y verificar plugins.

---

## 1. Requisitos nuevos de Filament v5

Antes de empezar, confirma que cumples:

| Requisito | Tu proyecto | Completado |
|-----------|------------|------------|
| Tailwind CSS v4.0+ | ✅ tailwindcss 4.2.1 | [ ] |
| Livewire v4.0+ | ✅ livewire 4.2.1 | [x] |
| Laravel v11.28+ | ✅ Laravel 12.x | [ ] |
| PHP 8.2+ | ✅ PHP ^8.4 | [ ] |

**Acción:** Livewire debe actualizarse a v4 **antes** de pasar al script de Filament v5.

---

## 2. Actualizar Livewire a v4 (prerrequisito)

Sigue la [guía de Livewire v4](https://livewire.laravel.com/docs/4.x/upgrading). Resumen de pasos:

### 2.1 Instalar Livewire v4

- [ ] Ejecutar: `docker compose exec app composer require livewire/livewire:^4.0`
- [ ] Ejecutar: `docker compose exec app php artisan optimize:clear`

### 2.2 Actualizar `config/livewire.php`

Cambios principales (v3 → v4):

- [ ] **Layout:** `'layout' => 'components.layouts.app'` → `'component_layout' => 'layouts::app'`  
  (o el layout que uses; el valor por defecto v4 es `layouts::app` → `resources/views/layouts/app.blade.php`).
- [ ] **Placeholder:** `'lazy_placeholder'` → `'component_placeholder'` (mismo valor, ej. `'livewire.placeholder'` o `null`).
- [ ] Revisar si quieres añadir: `'smart_wire_keys' => true` (por defecto en v4).
- [ ] Revisar nuevas opciones opcionales: `component_locations`, `component_namespaces`, `make_command`, `csp_safe` (ver guía).

### 2.3 Rutas de componentes full-page (si aplica)

- [ ] Si usas `Route::get('/ruta', Componente::class)`, valorar cambiar a `Route::livewire('/ruta', Componente::class)` (recomendado en v4).

### 2.4 Comportamiento de `wire:model`

- [ ] Si usas `wire:model` en contenedores que reciben eventos de hijos, considerar añadir el modificador `.deep` donde haga falta.
- [ ] Si usas `wire:model.blur` o `wire:model.change` y quieres el comportamiento anterior a v4, usar `wire:model.live.blur` / `wire:model.live.change`.

### 2.5 Componentes Livewire en Blade

- [ ] Asegurar que los tags de componentes Livewire estén **cerrados** (ej. `<livewire:nombre />` en lugar de `<livewire:nombre>` sin cerrar).

### 2.6 Scroll con `wire:navigate`

- [ ] Si usas `wire:scroll` para preservar scroll con `wire:navigate`, cambiar a `wire:navigate:scroll`.

### 2.7 Verificación Livewire v4

- [ ] `docker compose exec app composer show livewire/livewire` muestra versión ^4.x.
- [ ] Probar en navegador que los componentes Livewire y paneles Filament responden bien antes de seguir.

---

## 3. Plugins de Filament

Algunos plugins pueden no estar listos para v5. Revisa y actúa:

| Plugin actual | Versión v5 | Acción |
|---------------|------------|--------|
| `filament/spatie-laravel-media-library-plugin` | ^5.0 disponible | Actualizar a `^5.0` junto con Filament v5 |

- [ ] Si usas otros plugins de Filament, comprobar en Packagist/GitHub si tienen versión compatible con Filament v5.
- [ ] Opcional: Si un plugin no tiene v5, puedes quitarlo temporalmente de `composer.json`, sustituirlo, o esperar a que se actualice.

---

## 4. Script de actualización a Filament v5

### 4.1 Instalar el paquete de upgrade

- [ ] Ejecutar:
  ```bash
  docker compose exec app composer require filament/upgrade:"^5.0" -W --dev
  ```
  En Windows PowerShell, si `^` da problemas, usar: `filament/upgrade:"~5.0"`.

### 4.2 Ejecutar el script

- [ ] Ejecutar: `docker compose exec app vendor/bin/filament-v5`
- [ ] **Leer la salida completa:** el script indica comandos adicionales y cambios aplicados.
- [ ] Revisar los cambios que el script haya hecho en el código (git diff).

### 4.3 Comandos indicados por el script

El script suele pedir que ejecutes algo como (los exactos los muestra él):

- [ ] Ejecutar los comandos que imprima el script (por ejemplo):
  ```bash
  docker compose exec app composer require filament/filament:"^5.0" -W --no-update
  docker compose exec app composer update
  ```
  (En PowerShell, si hace falta: `filament/filament:"~5.0"`.)

### 4.4 Actualizar plugin Spatie Media Library (si lo usas)

- [ ] Ejecutar: `docker compose exec app composer require filament/spatie-laravel-media-library-plugin:"^5.0" -W`
- [ ] Resolver conflictos de dependencias si aparecen.

### 4.5 Quitar el paquete de upgrade

- [ ] Ejecutar: `docker compose exec app composer remove filament/upgrade --dev`

---

## 5. Revisión manual tras el script

- [ ] Revisar `config/filament.php` por claves eliminadas o renombradas en v5 (consultar docs v5 si hace falta).
- [ ] Buscar en el proyecto usos de APIs deprecadas o eliminadas que el script no haya tocado (p. ej. métodos de recursos, tablas, formularios).
- [ ] Si el script ha movido o renombrado archivos, comprobar namespaces y `getPages()` en recursos.
- [ ] Revisar temas CSS de paneles (Tailwind v4): que sigan usando `@source` y no sintaxis antigua.

---

## 6. Verificación final

- [ ] `docker compose exec app composer run quality:check` (o equivalente del proyecto).
- [ ] `docker compose exec app php artisan test --compact` (o los tests que uses).
- [ ] `docker compose exec app php artisan filament:optimize`
- [ ] `npm run build` (o `npm run dev`) si hay cambios de frontend; si corres npm en el contenedor: `docker compose exec app npm run build`.
- [ ] Probar en navegador los paneles que uses: Admin, AFIP, Embargos, Liquidaciones, Reportes, Mapuche, Bloqueos, Procesos, Toba, etc.
- [ ] Probar recursos que usen `SpatieMediaLibraryFileUpload` o columnas/entries de media (si aplica).

---

## 7. Resumen de prioridad

| Prioridad | Tarea |
|-----------|--------|
| Alta | Actualizar Livewire a v4 y actualizar `config/livewire.php`. |
| Alta | Ejecutar `filament-v5` y los comandos que indique. |
| Alta | Actualizar `filament/filament` y `filament/spatie-laravel-media-library-plugin` a ^5.0. |
| Media | Revisar cambios del script y ajustar código a mano si hace falta. |
| Media | Quitar `filament/upgrade` y verificar que no queden referencias. |
| Baja | Revisar opciones nuevas de Livewire v4 y Filament v5 (opcional). |

---

## 8. Referencias

- [Filament v5 Upgrade Guide](https://filamentphp.com/docs/5.x/upgrade-guide)
- [Livewire v4 Upgrading](https://livewire.laravel.com/docs/4.x/upgrading)
- Checklist anterior: [Filament v3 → v4](filament-v4-upgrade-checklist.md)

Cuando termines, puedes poner **Estado: Completada** al inicio de este documento y dejar las casillas marcadas como referencia.
