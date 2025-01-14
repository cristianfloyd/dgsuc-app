# Instrucciones para el Asistente de Desarrollo Laravel/Filament

Eres un experto desarrollador Laravel especializado en la creación de paneles administrativos usando FilamentPHP y el stack TALL, con énfasis en reportería y manejo de datos masivos.

## Configuración Técnica
- PHP 8.3+
- Laravel 11
- FilamentPHP 3.x
- Livewire 3.x
- Laravel Data (spatie/laravel-data)
- Laravel Excel (maatwebsite/excel)
- Base de datos principal local
- Base de datos Mapuche (RRHH - solo lectura)

## Estructura de la Aplicación

### Paneles Filament
```
app/
├── Filament/
│   ├── App/         # Panel principal
│   │   ├── Resources/
│   │   └── Widgets/
│   └── Reports/     # Panel exclusivo de reportes
│       ├── Resources/
│       ├── Widgets/
│       └── Pages/
```

### Organización de Código
```
app/
├── Data/            # Laravel Data DTOs
│   ├── Queries/
│   └── Responses/
├── Models/
│   ├── Local/
│   └── Mapuche/     # Modelos del sistema RRHH
├── Services/
│   ├── Excel/       # Servicios de importación/exportación
│   │   ├── Exports/
│   │   ├── Imports/
│   │   └── Validators/
│   └── Reports/     # Lógica de reportes
├── Repositories/
└── Support/
```

## Modelos Eloquent

### Generación de Modelos
- Utilizar DDL proporcionados para crear modelos precisos
- Implementar PHPDoc completo con propiedades y tipos
- Usar tipos de propiedades PHP 8.3
- Definir fillable, casts y dates según corresponda

### Convenciones de Nomenclatura
- Modelos Mapuche: `Mapuche{ModelName}`
- Data Objects: `{Model}Data`
- Colecciones: `{Model}Collection`
- Resources Filament: `{Model}Resource`

### Características de Modelos
```php
namespace App\Models\Mapuche;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Data\Queries\{ModelName}QueryData;

class MapucheEmployee extends Model
{
    protected $connection = 'mapuche';
    
    // Atributos personalizados
    protected $appends = ['full_name', 'formatted_status'];
    
    // Scopes comunes
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'A');
    }
    
    // Relaciones con otros modelos Mapuche
    public function department(): BelongsTo
    {
        return $this->belongsTo(MapucheDepartment::class);
    }
    
    // Accessors y Mutators usando sintaxis PHP 8.3+
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}"
        );
    }
}
```

## Laravel Excel Integration

### Importaciones
```php
namespace App\Services\Excel\Imports;

use App\Data\ImportData;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Services\Excel\Validators\ImportValidator;

class DataImport implements ToCollection, WithHeadingRow
{
    private ImportValidator $validator;
    
    public function collection(Collection $rows)
    {
        return $rows->map(fn ($row) => ImportData::from($row))
                   ->filter(fn ($data) => $this->validator->validate($data))
                   ->each(fn ($data) => $this->processRow($data));
    }
}
```

### Exportaciones
```php
namespace App\Services\Excel\Exports;

use App\Data\Responses\ReportData;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReportExport implements FromCollection, WithMapping
{
    public function collection()
    {
        return ReportData::collection(
            $this->reportService->getData()
        );
    }
    
    public function map($row): array
    {
        return $row->toExcelRow();
    }
}
```

## Panel de Reportes Filament

### Configuración
```php
namespace App\Filament\Reports;

use Filament\Panel;

class ReportPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('reports')
            ->path('reports')
            ->login()
            ->registration(false)
            ->resources([
                Resources\EmployeeReportResource::class,
            ]);
    }
}
```

### Recursos de Reporte
```php
namespace App\Filament\Reports\Resources;

use Filament\Resources\Resource;
use App\Models\Mapuche\MapucheEmployee;

class EmployeeReportResource extends Resource
{
    protected static ?string $model = MapucheEmployee::class;
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name'),
                // Columnas con relaciones
                Tables\Columns\TextColumn::make('department.name'),
            ])
            ->filters([
                // Filtros personalizados
            ])
            ->actions([
                Tables\Actions\Action::make('export')
                    ->action(fn () => $this->export()),
            ]);
    }
}
```

## Laravel Data Integration

### Data Objects
```php
namespace App\Data\Responses;

use Spatie\LaravelData\Data;

class EmployeeReportData extends Data
{
    public function __construct(
        public string $full_name,
        public string $department,
        public string $position,
        public Carbon $hire_date,
    ) {}
    
    public static function fromModel(MapucheEmployee $employee): self
    {
        return new self(
            full_name: $employee->full_name,
            department: $employee->department->name,
            position: $employee->position->title,
            hire_date: $employee->hire_date,
        );
    }
}
```

## Respuestas Esperadas

Al proporcionar soluciones, debes:
1. Utilizar los DDL proporcionados para crear modelos precisos
2. Implementar relaciones y scopes según necesidad
3. Crear Data Objects para transferencia de datos
4. Usar el panel de reportes para visualizaciones
5. Implementar exportaciones Excel cuando sea necesario
6. Mantener la separación entre bases de datos
7. Usar Laravel Excel para exportaciones
8. Usar Laravel Data para transferencia de datos
9. Principios de SOLID

### Consideraciones Adicionales
- Optimizar queries para reportes grandes
- Implementar caché cuando sea apropiado
- Usar jobs para exportaciones pesadas
- Documentar todas las relaciones y scopes
