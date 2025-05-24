# Implementación de Consulta de Licencias Vigentes

## Descripción General

El recurso `LicenciaVigenteResource` proporciona una interfaz para consultar las licencias vigentes de agentes en el período fiscal actual. Se ha implementado utilizando un enfoque desacoplado basado en servicios, que conecta con la base de datos Mapuche y presenta los resultados a través de una interfaz Filament.

A diferencia de la mayoría de los recursos Filament que están basados en modelos Eloquent directos, este recurso utiliza un Data Transfer Object (DTO) y un Service Layer para abstraer la complejidad de las consultas SQL a la base de datos Mapuche.

## Estructura de Archivos

```bash
app/
├── Data/
│   └── Responses/
│       └── LicenciaVigenteData.php       # DTO para representar licencias
│
├── Filament/
│   └── Reportes/
│       └── Resources/
│           ├── LicenciaVigenteResource.php   # Recurso Filament
│           └── LicenciaVigenteResource/
│               └── Pages/
│                   └── ListLicenciaVigentes.php  # Página de listado
│
├── Models/
│   └── Mapuche/
│       └── MapucheConfig.php             # Modelo de configuración y parámetros
│
├── Services/
│   ├── Excel/
│   │   └── Exports/
│   │       └── LicenciasVigentesExport.php   # Exportador a Excel
│   │
│   └── Mapuche/
│       └── LicenciaService.php           # Servicio para obtener licencias
````

## Componentes Principales

### 1. Data Transfer Object (DTO)

El objeto `LicenciaVigenteData` encapsula los datos de una licencia vigente y proporciona métodos para transformar y presentar estos datos:

```php
// app/Data/Responses/LicenciaVigenteData.php
class LicenciaVigenteData extends Data
{
    public function __construct(
        public int $nro_legaj,
        public int $inicio,
        public int $final,
        public bool $es_legajo,
        public int $condicion,
        // ... otras propiedades
    ) { }
    
    // Método para obtener descripción legible
    public function getDescripcionCondicion(): string
    {
        return match($this->condicion) {
            5 => 'Maternidad',
            // ... otras condiciones
        };
    }
    
    // Método para exportación a Excel
    public function toExcelRow(): array
    {
        // ...
    }
}
```

### 2. Servicio de Licencias

El `LicenciaService` contiene la lógica para consultar las licencias vigentes a la base de datos Mapuche:

```php
// app/Services/Mapuche/LicenciaService.php
class LicenciaService
{
    public function getLicenciasVigentes(array $legajos): DataCollection
    {
        // Obtener parámetros de configuración
        $fechaInicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        $fechaFin = MapucheConfig::getFechaFinPeriodoCorriente();
        
        // Construir y ejecutar consulta SQL
        // ...
        
        // Mapear resultados a DTOs
        return LicenciaVigenteData::fromResultados($resultados);
    }
}
```

### 3. Configuración Mapuche

La clase `MapucheConfig` proporciona métodos para obtener parámetros de configuración del sistema Mapuche:

```php
// app/Models/Mapuche/MapucheConfig.php
class MapucheConfig
{
    // Obtener valores de parámetros
    public static function getParametroRrhh(string $section, string $parameter, $default = null)
    {
        // ...
    }
    
    // Fechas del período corriente
    public static function getFechaInicioPeriodoCorriente(): string
    {
        // ...
    }
    
    // Variantes de licencias
    public static function getVarLicencias10Dias(): string
    {
        // ...
    }
}
```

### 4. Exportador Excel

La clase `LicenciasVigentesExport` permite exportar los resultados a Excel:

```php
// app/Services/Excel/Exports/LicenciasVigentesExport.php
class LicenciasVigentesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    // ...
    
    public function collection()
    {
        return collect($this->licencias->all());
    }
    
    public function map($row): array
    {
        if ($row instanceof LicenciaVigenteData) {
            return $row->toExcelRow();
        }
        // ...
    }
}
```

### 5. Recurso Filament

El recurso `LicenciaVigenteResource` define la interfaz de usuario y cómo se presentan los datos:

```php
// app/Filament/Reportes/Resources/LicenciaVigenteResource.php
class LicenciaVigenteResource extends Resource
{
    // Definición de tabla, filtros, acciones...
    
    public static function getTableRecords(): \Illuminate\Support\Collection
    {
        $legajos = session('licencias_vigentes_legajos', []);
        
        if (empty($legajos)) {
            return collect();
        }
        
        $licenciaService = app(LicenciaService::class);
        $licencias = $licenciaService->getLicenciasVigentes($legajos);
        
        return collect($licencias->all());
    }
}
```

## Flujo de Trabajo

1. El usuario accede a la página "Licencias Vigentes" en el panel de Reportes.
2. Utiliza el botón "Consultar Legajos" para ingresar los números de legajo que desea consultar.
3. El sistema:
   - Almacena los legajos en la sesión
   - Invoca al `LicenciaService` para obtener las licencias vigentes
   - Procesa los resultados a través del DTO `LicenciaVigenteData`
   - Presenta los resultados en una tabla Filament
4. El usuario puede:
   - Filtrar los resultados por tipo de licencia y asociación
   - Exportar los resultados a Excel

## Beneficios de la Implementación

- **Desacoplamiento**: La lógica de negocio está separada de la presentación
- **Mantenibilidad**: Cada componente tiene una responsabilidad única
- **Testabilidad**: Los servicios y DTOs pueden ser testeados de forma aislada
- **Seguridad**: Las consultas SQL están parametrizadas para evitar inyecciones
- **Reutilización**: El servicio puede ser utilizado por otros componentes

## Consideraciones Futuras

- Implementar caché para mejorar el rendimiento en consultas repetidas
- Añadir más filtros y opciones de visualización
- Integrar con otros reportes y paneles
- Implementar exportación a otros formatos (PDF, CSV)

## Consideraciones Importantes

### Configuración para recursos sin modelo Eloquent

Como este recurso no está asociado a un modelo Eloquent directo (`protected static ?string $model = null`), es necesario implementar algunos métodos adicionales para evitar errores:

1. **Sobrescritura de `getEloquentQuery()`** en el recurso principal:

```php
// app/Filament/Reportes/Resources/LicenciaVigenteResource.php
public static function getEloquentQuery(): Builder
{
    // Devolvemos una query vacía que no afecta el funcionamiento
    return DB::table('users')->whereRaw('1=0')->toBase();
}
```

2. **Sobrescritura de `getTableQuery()`** en la página ListRecords:

```php
// app/Filament/Reportes/Resources/LicenciaVigenteResource/Pages/ListLicenciaVigentes.php
protected function getTableQuery(): Builder
{
    // También devolvemos una query vacía
    return DB::table('users')->whereRaw('1=0')->toBase();
}
```

Estos métodos son necesarios para evitar errores como `Class "App\Models\LicenciaVigente" not found`, ya que Filament intenta utilizarlos en su flujo de trabajo interno.

### Manejo de valores nulos en callbacks

Al trabajar con un recurso que no tiene un modelo Eloquent asociado, es importante tener en cuenta que Filament evalúa algunas callbacks de la tabla antes de que los datos estén realmente disponibles. Esto puede causar errores si las callbacks esperan un tipo específico de objeto (como nuestro DTO) pero reciben `null`.

Para evitar este problema, todas las callbacks que acceden a propiedades de los registros deben verificar primero el tipo de dato:

```php
// ❌ Código que puede fallar
TextColumn::make('nro_cargo')
    ->visible(fn (LicenciaVigenteData $record): bool => !$record->es_legajo)

// ✅ Código seguro
TextColumn::make('nro_cargo')
    ->visible(fn ($record): bool => $record instanceof LicenciaVigenteData && !$record->es_legajo)
```

Este patrón debe aplicarse en:
- Callbacks de visibilidad de columnas
- Callbacks de formateo
- Callbacks de color
- Callbacks de filtros

### Errores comunes

El siguiente error indica que una callback está intentando acceder a un registro que todavía no existe:

```
TypeError: Argument #1 ($record) must be of type App\Data\Responses\LicenciaVigenteData, null given
```

Y este error indica que Filament está buscando un modelo que no existe:

```
Class "App\Models\LicenciaVigente" not found
```

## Comandos de Generación

El recurso fue generado inicialmente con el comando Artisan de Filament:

```bash
php artisan make:filament-resource LicenciaVigente --generate --panel=Reportes
```

Y luego personalizado para trabajar con el enfoque basado en servicios y DTOs.
