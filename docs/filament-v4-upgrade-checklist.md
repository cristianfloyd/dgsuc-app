# Checklist de actualización Filament v3 → v4

Análisis realizado según la [guía oficial de Filament v4](https://filamentphp.com/docs/4.x/upgrade-guide).

## Objetivo de esta migración

Esta actualización tiene como **objetivo dejar el proyecto estable en Filament v4** para, en un siguiente paso, poder actualizar a **Filament v5** con menos fricción. El camino es:

1. **Fase actual:** v3 → v4 (este checklist).
2. **Fase siguiente:** v4 → v5 (cuando se decida, usando la [guía de actualización a v5](https://filamentphp.com/docs/5.x/upgrade-guide)).

Mantener el código alineado con v4 (y con las prácticas recomendadas en su guía) facilita la futura migración a v5.

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
| `default_filesystem_disk`                                  | ✅ Restaurado v3: `env('FILAMENT_FILESYSTEM_DISK', 'public')`                      |
| Tailwind CSS en proyecto                                   | ✅ tailwindcss ^4.2.1 y @tailwindcss/vite ^4.2.1                                  |
| Uso de `FileUpload`/`ImageColumn` con disco no local       | ✅ Se usa `visibility('private')` o `visibility('public')` explícito donde aplica |
| Métodos deprecados de tabla (getTableRecordUrlUsing, etc.) | ✅ No se usan en el código                                                        |

---

## ✅ Completado en esta sesión

| Paso | Acción |
|------|--------|
| Quitar filament/upgrade | Ya no está en `composer.json` (require-dev ni script). |
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

En `config/filament.php` se restauró el comportamiento v3: `'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public')`. Para usar disco `local` por defecto, define `FILAMENT_FILESYSTEM_DISK=local` en `.env`.

---

### 6. Opciones de generación de código (file_generation)

En `config/filament.php` tienes:

```php
'file_generation' => [
    'flags' => [],
],
```

La guía v4 recomienda, para mantener comportamiento similar a v3 al generar recursos/paneles, usar flags como:

- `FileGenerationFlag::EMBEDDED_PANEL_RESOURCE_SCHEMAS`
- `FileGenerationFlag::EMBEDDED_PANEL_RESOURCE_TABLES`
- `FileGenerationFlag::PANEL_CLUSTER_CLASSES_OUTSIDE_DIRECTORIES`
- `FileGenerationFlag::PANEL_RESOURCE_CLASSES_OUTSIDE_DIRECTORIES`
- `FileGenerationFlag::PARTIAL_IMPORTS`

**Acción:** Si quieres que los nuevos recursos generados con Artisan sigan el estilo v3 (schemas/tablas embebidos, clases fuera de directorios, etc.), añade los flags que apliquen en `file_generation.flags` y el `use` de `Filament\Support\Commands\FileGenerators\FileGenerationFlag`. Si ya migraste la estructura con `php artisan filament:upgrade-directory-structure-to-v4`, no necesitas los flags de “outside directories”.

---

### 7. ~~Configuración eliminada en v4 (upload_directory, upload_max_filesize, model_morph_key)~~ (revisado)

En `config/filament.php.old` existían `upload_directory`, `upload_max_filesize` y `model_morph_key`. No hay referencias en el código a `config('filament.upload_directory')` ni a las otras claves. Si en el futuro necesitas equivalente a `upload_directory`, configúralo en el disco en `config/filesystems.php`; para límite de tamaño, en validación del campo FileUpload.

---

### 8. ~~Autorización en recursos: canCreate() → getCreateAuthorizationResponse()~~ (hecho)

~~En v4, los métodos `can*()` (como `canCreate()`) no siempre se usan.~~ En `LicenciaVigenteResource` se sustituyó `canCreate()` por `getCreateAuthorizationResponse()` que devuelve `Response::deny()`.

---

### 9. Estructura de directorios (opcional)

Si no lo has hecho ya, puedes migrar a la estructura de directorios por defecto de v4:

```bash
php artisan filament:upgrade-directory-structure-to-v4 --dry-run
```

Revisar el resultado y, si es correcto:

```bash
php artisan filament:upgrade-directory-structure-to-v4
```

Después, comprobar referencias a clases en namespaces (p. ej. con PHPStan) y corregir imports o nombres de clase si algo queda desactualizado.

---

### 10. ~~Cambios de comportamiento por defecto~~ (aplicados en AppServiceProvider)

En `AppServiceProvider::boot()` se añadieron (comportamiento tipo v3):

- **Filtros de tabla:** `Table::configureUsing(fn (Table $table) => $table->deferFilters(false));` — los filtros se aplican al momento.
- **Section / Grid / Fieldset:** `liberatedFromContainerGrid()` para que ocupen todo el ancho del grid del formulario.

Opcionalmente puedes añadir más: `paginationPageOptions([..., 'all'])`, `defaultKeySort(false)`, o `Field::configureUsing(..., ->uniqueValidationIgnoresRecordByDefault(false))` si los necesitas.

---

## Resumen de acciones prioritarias

1. ~~**Alta:** Actualizar referencias de `tableFilters` → `filters`~~ ✅ Hecho.
2. ~~**Alta:** Migrar el tema del panel Reportes a Tailwind v4~~ ✅ Hecho.
3. ~~**Media:** Quitar `filament/upgrade` y el script~~ ✅ Ya no estaban.
4. ~~**Media:** Decidir `default_filesystem_disk`~~ ✅ Usando `FILAMENT_FILESYSTEM_DISK` y `'public'`.
5. ~~**Media:** Sustituir `canCreate()` por `getCreateAuthorizationResponse()` en LicenciaVigenteResource~~ ✅ Hecho.
6. ~~**Baja:** Revisar `upload_directory`, etc.~~ ✅ Revisado: no se usan en código.
7. ~~**Opcional:** activeTab (revisado, no aplica); configureUsing en AppServiceProvider~~ ✅ Hecho (deferFilters + liberatedFromContainerGrid).

**Pendiente opcional:** `file_generation.flags` (si quieres estilo v3 al generar recursos).

Cuando esta fase v4 esté cerrada y estable, el siguiente paso será seguir la [guía de actualización a Filament v5](https://filamentphp.com/docs/5.x/upgrade-guide).

Cuando quieras, ejecutar `composer run quality:check`, `php artisan test` y comprobar en navegador los paneles.

---

## Fase: Compatibilidad v4 y estructura de directorios

Objetivo: **revisar que todos los paneles y recursos sean compatibles con Filament v4** y migrar a la **nueva estructura de directorios v4** para dejar la base lista antes de pasar a v5.

### Orden recomendado

1. **Revisar paneles** — Comprobar que cada panel use la API v4 y que rutas, middleware y discovery estén correctos.
2. **Revisar recursos** — Comprobar que recursos, páginas y relation managers usen la API v4 (form/table/infolist, autorización, etc.).
3. **Migrar estructura de directorios** — Pasar a la estructura v4 (recursos con subcarpeta por recurso y páginas dentro).

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

**Qué revisar en cada panel (v4):**

- [ ] Uso de `Panel::` y `PanelProvider` (sin APIs deprecadas).
- [ ] `discoverResources(in:, for:)`, `discoverPages(in:, for:)`, `discoverWidgets(in:, for:)` con namespaces correctos.
- [ ] Middleware: que no falte ni sobre middleware que v4 ya aplica por defecto (consultar [docs v4](https://filamentphp.com/docs/4.x)).
- [ ] Rutas del panel (`path`) y que no choquen entre paneles.
- [ ] Que las páginas/recursos/widgets registrados existan en las rutas de discovery (p. ej. Embargos no tiene carpeta `Pages` bajo `Filament/Embargos`; si hay páginas, añadir la carpeta o registrar a mano).

Tras revisar, probar cada panel en navegador (login, listado de recursos, crear/editar, widgets del dashboard).

---

### 2. Revisión de recursos (compatibilidad v4)

**Qué revisar en cada recurso:**

- [ ] Uso de `Filament\Resources\Resource` y de `form()`, `table()` (e `infolist()` si aplica) con la API v4 (Schemas, componentes de Forms/Tables v4).
- [ ] Páginas del recurso (`List*, Create*, Edit*, View*`) en el namespace correcto y que extiendan las clases de Filament v4.
- [ ] Relation managers: que usen la API v4 y estén en la carpeta/namespace que el panel descubre.
- [ ] Autorización: preferir `get*AuthorizationResponse()` en lugar de `can*()` donde la guía v4 lo indique (ya aplicado en LicenciaVigenteResource).
- [ ] Parámetros de URL: usar `filters`, `tab`, `search`, `sort` (no `tableFilters`, `activeTab`, etc.) en enlaces y en `request()` (ya aplicado donde había `tableFilters`).
- [ ] Imports y referencias a clases del mismo recurso (Pages, RelationManagers): tras cambiar estructura de directorios, pueden cambiar namespaces; revisar con PHPStan.

Recursos compartidos (panel SUC): están en `app/Filament/Resources/` (Dh03, Dh11, Dh12, Dh13, Dh21, Dh41, MapucheGrupo, Personal, Mapuche/Dh05, etc.). Tras la migración de estructura, estos deberían quedar bajo una estructura v4 consistente (ver siguiente sección).

---

### 3. Migración a la estructura de directorios v4

En v4 la estructura por defecto pone cada recurso en su propia carpeta, con las páginas del recurso dentro, por ejemplo:

**Estructura actual (ejemplo):**
```
app/Filament/Reportes/Resources/
  ReporteConceptoListados/
    ReporteConceptoListadoResource.php
  Pages/
    EditReporteConceptoListado.php
    ...
```

**Estructura v4 (ejemplo):**
```
app/Filament/Reportes/Resources/
  ReporteConceptoListadoResource/
    ReporteConceptoListadoResource.php
    Pages/
      EditReporteConceptoListado.php
      ListReporteConceptoListados.php
      ...
```

**Cómo migrar:**

- El comando oficial es `php artisan filament:upgrade-directory-structure-to-v4` (primero con `--dry-run`). Ese comando viene del paquete **filament/upgrade**, que ya no está instalado.
- **Opción A:** Reinstalar temporalmente el paquete, ejecutar el comando y luego quitarlo de nuevo:
  ```bash
  composer require filament/upgrade:"^4.0" -W --dev
  php artisan filament:upgrade-directory-structure-to-v4 --dry-run   # revisar salida
  php artisan filament:upgrade-directory-structure-to-v4             # aplicar
  composer remove filament/upgrade --dev
  ```
- **Opción B:** Migrar a mano: para cada recurso, crear la carpeta `{NombreRecurso}Resource`, mover el `*Resource.php` ahí, crear la subcarpeta `Pages/` y mover las páginas del recurso; actualizar namespaces y `getPages()` / referencias entre clases; luego actualizar los PanelProviders solo si cambian las rutas de discovery (en v4 suele seguir siendo `discoverResources(in: ..., for: 'App\\Filament\\...\\Resources')`).

**Después de migrar:**

- [ ] Ejecutar `php artisan filament:optimize` (o `filament:optimize-clear` y luego usar la app).
- [ ] Ejecutar PHPStan para detectar referencias rotas (namespaces, imports).
- [ ] Probar cada panel: listados, crear, editar, relation managers.
- [ ] Dejar documentado en este checklist que la estructura ya es la v4 para facilitar el paso a v5.

**Resumen de esta fase:** Primero revisar paneles y recursos para compatibilidad v4; luego migrar a la nueva estructura de directorios (reinstalando temporalmente `filament/upgrade` para el comando o migrando a mano). Con eso el proyecto queda alineado con v4 y preparado para la futura actualización a v5.