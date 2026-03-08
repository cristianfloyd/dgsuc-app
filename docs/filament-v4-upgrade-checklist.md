# Checklist de actualización Filament v3 → v4

Análisis realizado según la [guía oficial de Filament v4](https://filamentphp.com/docs/4.x/upgrade-guide).

**Estado:** Migración v4 **completada**. El proyecto corre en Filament v4; se dejó atrás la compatibilidad con v3.

> **Nota:** La aplicación corre en Docker. Para comandos Artisan usar: `docker compose exec app php artisan <comando>`.

## Objetivo de esta migración

Esta actualización tiene como **objetivo dejar el proyecto estable en Filament v4** para, en un siguiente paso, poder actualizar a **Filament v5** con menos fricción. El camino es:

1. **Fase actual:** v3 → v4 (este checklist) — **completada**.
2. **Fase siguiente:** v4 → v5 (cuando se decida, usando la [guía de actualización a v5](https://filamentphp.com/docs/5.x/upgrade-guide)).

---

## ✅ Pasos ya cumplidos


| Requisito / Paso                                           | Estado                                                                           |
| ---------------------------------------------------------- | -------------------------------------------------------------------------------- |
| PHP 8.2+                                                   | ✅ PHP ^8.4 en composer.json                                                      |
| Laravel v11.28+                                            | ✅ Laravel ^12.0                                                                  |
| filament/filament ^4.0                                     | ✅ Instalado                                                                      |
| filament/spatie-laravel-media-library-plugin ^4.0          | ✅ Instalado                                                                      |
| Config publicada                                           | ✅ `config/filament.php` existe (formato v4)                                      |
| Sección `file_generation` en config                        | ✅ Presente (con `flags` vacío)                                                   |
| `default_filesystem_disk`                                  | ✅ `env('FILAMENT_FILESYSTEM_DISK', 'public')` en config                           |
| Tailwind CSS en proyecto                                   | ✅ tailwindcss ^4.2.1 y @tailwindcss/vite ^4.2.1                                  |
| Uso de `FileUpload`/`ImageColumn` con disco no local       | ✅ Se usa `visibility('private')` o `visibility('public')` explícito donde aplica |
| Métodos deprecados de tabla (getTableRecordUrlUsing, etc.) | ✅ No se usan en el código                                                        |

---

## ✅ Completado en esta sesión

| Paso | Acción |
|------|--------|
| Quitar filament/upgrade | Eliminado tras ejecutar la migración de estructura (Docker: `composer remove filament/upgrade --dev`). |
| Estructura de directorios v4 | Ejecutado `filament:upgrade-directory-structure-to-v4`. Recursos en subcarpetas; Pages en carpeta padre; ComprobanteNominaModelResource con referencias a Pages corregidas. |
| Tema panel Reportes | `theme.css` ya usa `@source` y no `@config` (Tailwind v4). |
| Parámetros URL `tableFilters` → `filters` | EditImportData, ManageMapucheGrupoLegajos y ReporteConceptoListadoResource actualizados. |
| LicenciaVigenteResource | `canCreate()` sustituido por `getCreateAuthorizationResponse()` que devuelve `Response::deny()`. |
| activeTab (§4) | Revisado: SicossControles no usa el param en URL; no aplica cambio. |
| default_filesystem_disk (§5) | Restaurado v3: `env('FILAMENT_FILESYSTEM_DISK', 'public')` en `config/filament.php`. |
| upload_directory etc. (§7) | Revisado: no se usan en código; documentado en checklist. |
| AppServiceProvider (§10) | `Table::deferFilters(false)`; Section/Grid/Fieldset `liberatedFromContainerGrid()`. |

---

## ❌ Pasos pendientes o a revisar

### 1. ~~Quitar el paquete de upgrade~~ (hecho)

~~La guía indica que tras ejecutar el script de actualización se puede eliminar `filament/upgrade`.~~ Ya no está en `require-dev` ni el script `post-autoload-dump` lo invoca.

### 2. ~~Actualizar tema Reportes a Tailwind v4~~ (hecho)

~~El tema del panel Reportes sigue usando la sintaxis de Tailwind v3.~~ `resources/css/filament/reportes/theme.css` ya tiene `@source` y no usa `@config`.

### 3. ~~Parámetros de URL tableFilters → filters~~ (hecho)

~~Filament v4 renombró varios query params.~~ Corregido en EditImportData, ManageMapucheGrupoLegajos y ReporteConceptoListadoResource.

### 4. ~~Parámetro activeTab en URLs~~ (revisado, no aplica)

SicossControles usa `$this->activeTab` solo como propiedad interna del componente Livewire; no se construyen URLs con ese query param. El renombrado v4 (`activeTab` → `tab`) aplica a páginas de recurso List/ManageRelation; en esta página no hay cambio necesario.

---

### 5. ~~Comportamiento de disco por defecto~~ (hecho)

En `config/filament.php`: `'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public')`. Para usar disco `local`, define `FILAMENT_FILESYSTEM_DISK=local` en `.env`.

---

### 6. ~~Opciones de generación de código (file_generation)~~ (v4 por defecto)

Se dejó atrás el estilo v3. En `config/filament.php` se mantiene `'file_generation' => ['flags' => []]`. Los nuevos recursos generados con Artisan seguirán la convención v4. No se añaden flags de compatibilidad v3.


---

### 7. ~~Configuración eliminada en v4 (upload_directory, upload_max_filesize, model_morph_key)~~ (revisado)

En `config/filament.php.old` existían `upload_directory`, `upload_max_filesize` y `model_morph_key`. No hay referencias en el código a `config('filament.upload_directory')` ni a las otras claves. Si en el futuro necesitas equivalente a `upload_directory`, configúralo en el disco en `config/filesystems.php`; para límite de tamaño, en validación del campo FileUpload.

---

### 8. ~~Autorización en recursos: canCreate() → getCreateAuthorizationResponse()~~ (hecho)

~~En v4, los métodos `can*()` (como `canCreate()`) no siempre se usan.~~ En `LicenciaVigenteResource` se sustituyó `canCreate()` por `getCreateAuthorizationResponse()` que devuelve `Response::deny()`.

---

### 9. ~~Estructura de directorios~~ (hecho)

Migración aplicada con `php artisan filament:upgrade-directory-structure-to-v4` (tras instalar temporalmente `filament/upgrade`). Los recursos están en subcarpetas (ej. `Documentations/Documentations/`, `ReporteConceptoListados/ReporteConceptoListados/`); las Pages permanecen en la carpeta padre. Recursos del panel SUC (`app/Filament/Resources/`) se mantienen en estructura plana; discovery los encuentra correctamente.

---

### 10. ~~Cambios de comportamiento por defecto~~ (aplicados en AppServiceProvider)

En `AppServiceProvider::boot()`:

- **Filtros de tabla:** `Table::configureUsing(fn (Table $table) => $table->deferFilters(false));`
- **Section / Grid / Fieldset:** `liberatedFromContainerGrid()`

Opcional: `paginationPageOptions([..., 'all'])`, `defaultKeySort(false)`, etc. si se necesitan.

---

## Resumen de acciones prioritarias

1. ~~**Alta:** Actualizar referencias de `tableFilters` → `filters`~~ ✅ Hecho.
2. ~~**Alta:** Migrar el tema del panel Reportes a Tailwind v4~~ ✅ Hecho.
3. ~~**Media:** Quitar `filament/upgrade` y el script~~ ✅ Ya no estaban.
4. ~~**Media:** Decidir `default_filesystem_disk`~~ ✅ Usando `FILAMENT_FILESYSTEM_DISK` y `'public'`.
5. ~~**Media:** Sustituir `canCreate()` por `getCreateAuthorizationResponse()` en LicenciaVigenteResource~~ ✅ Hecho.
6. ~~**Baja:** Revisar `upload_directory`, etc.~~ ✅ Revisado: no se usan en código.
7. ~~**Opcional:** activeTab (revisado, no aplica); configureUsing en AppServiceProvider~~ ✅ Hecho (deferFilters + liberatedFromContainerGrid).

~~**Pendiente opcional:** `file_generation.flags`~~ No aplica; se usa convención v4.

**Siguiente paso (cuando se decida):** Seguir la [guía de actualización a Filament v5](https://filamentphp.com/docs/5.x/upgrade-guide).

**Verificación recomendada (en Docker):** `docker compose exec app composer run quality:check`, `docker compose exec app php artisan test --compact`, `docker compose exec app php artisan filament:optimize`, y comprobar paneles en el navegador.

---

## Fase: Compatibilidad v4 y estructura de directorios — ✅ Completada

Objetivo: **revisar que todos los paneles y recursos sean compatibles con Filament v4** y migrar a la **nueva estructura de directorios v4**.

### Realizado

1. **Revisión de paneles** — PanelProviders usan API v4 (discovery, middleware). Sin APIs deprecadas.
2. **Revisión de recursos** — Recursos y páginas con API v4; parám. URL y autorización actualizados donde aplicaba.
3. **Migración de estructura** — Ejecutado `filament:upgrade-directory-structure-to-v4` (con `filament/upgrade` temporal); luego paquete eliminado.

---

### 1. Revisión de paneles (compatibilidad v4)

Paneles actuales y rutas de discovery:

| Panel      | Provider                    | Resources (in)                    | Pages (in)                 | Widgets (in)                 |
|-----------|-----------------------------|----------------------------------|----------------------------|-----------------------------|
| admin     | AdminPanelProvider          | `Filament/Admin/Resources`       | `Filament/Admin/Pages`     | `Filament/Admin/Widgets`     |
| afip      | AfipPanelProvider           | `Filament/Afip/Resources`       | `Filament/Afip/Pages`      | `Filament/Afip/Widgets`      |
| embargos  | EmbargosPanelProvider       | `Filament/Embargos/Resources`   | `Filament/Embargos/Pages`   | `Filament/Embargos/Widgets`  |
| liquidaciones | LiquidacionesPanelProvider | `Filament/Liquidaciones/Resources` | `Filament/Liquidaciones/Pages` | `Filament/Liquidaciones/Widgets` |
| reportes  | ReportesPanelProvider       | `Filament/Reportes/Resources`   | `Filament/Reportes/Pages`   | `Filament/Reportes/Widgets`  |
| suc       | SucPanelProvider            | `Filament/Resources` (compartido) | `Filament/Pages`          | `Filament/Widgets`          |
| mapuche   | MapuchePanelProvider       | `Filament/Mapuche/Resources`    | `Filament/Mapuche/Pages`   | `Filament/Mapuche/Widgets`   |
| bloqueos  | BloqueosPanelProvider      | `Filament/Bloqueos/Resources`   | `Filament/Bloqueos/Pages`   | (sin Widgets descubiertos)   |
| procesos  | ProcesosPanelProvider      | `Filament/ProcesosPanel/Resources` | `Filament/ProcesosPanel/Pages` | `Filament/ProcesosPanel/Widgets` |
| toba      | TobaPanelProvider           | `Filament/Toba/Resources`       | `Filament/Toba/Pages`      | `Filament/Toba/Widgets`      |

**Revisado:** Uso de `Panel`/`PanelProvider`, discovery y middleware correctos; rutas sin conflictos. Probar en navegador cada panel que se use.

---

### 2. Revisión de recursos (compatibilidad v4)

**Revisado:** Recursos con API v4; autorización y parámetros URL actualizados. ComprobanteNominaModelResource: namespace y `getPages()` corregidos tras la migración. Recursos del panel SUC en `app/Filament/Resources/` se mantienen en estructura plana; discovery correcto.

---

### 3. ~~Migración a la estructura de directorios v4~~ (hecho)

Aplicada con `filament:upgrade-directory-structure-to-v4` (paquete `filament/upgrade` instalado temporalmente y luego eliminado). Estructura resultante: cada recurso en subcarpeta con el mismo nombre (ej. `Documentations/Documentations/`, `ReporteConceptoListados/ReporteConceptoListados/`); las Pages permanecen en la carpeta padre; los recursos referencian las Pages por namespace completo cuando aplica.

**Recomendado:** `docker compose exec app php artisan filament:optimize`, `composer run quality:check`, `php artisan test` y pruebas en navegador. Proyecto alineado con Filament v4; siguiente paso cuando se decida: [guía v4 → v5](https://filamentphp.com/docs/5.x/upgrade-guide).