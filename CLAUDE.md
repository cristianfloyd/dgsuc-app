# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## System Architecture

This is a Laravel 11 application built for HR reporting and payroll management for Universidad de Buenos Aires, using FilamentPHP for multiple admin panels and PostgreSQL with multi-database architecture.

### Technology Stack
- **Framework**: Laravel 11 with PHP 8.3+
- **Frontend**: FilamentPHP 3.x, Livewire 3.5+, TailwindCSS + DaisyUI
- **Database**: PostgreSQL with dual connections:
  - `pgsql` (default): Application data in `suc_app,informes_app` schemas
  - `pgsql-mapuche`: HR system data (read-only connection)
- **Key Libraries**: Laravel Excel, Spatie Laravel Data, Laravel Jetstream

### Panel Structure
The application uses multiple Filament panels:
- **Admin Panel** (`/admin`): User management, system configuration
- **AFIP Panel** (`/afip`): Tax reporting, SICOSS generation
- **Bloqueos Panel** (`/bloqueos`): Employee restrictions management  
- **Embargos Panel** (`/embargos`): Salary garnishment processing
- **Liquidaciones Panel** (`/liquidaciones`): Payroll liquidations
- **Mapuche Panel** (`/mapuche`): HR system integration
- **Reportes Panel** (`/reportes`): General reporting system

## Development Commands

### Common Laravel Commands
- `php artisan serve` - Run development server
- `php artisan test` - Run PHPUnit tests
- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production

### Custom Artisan Commands
- `php artisan sicoss:generar {legajo?}` - Generate SICOSS file for specific employee or all
- `php artisan sicoss:generar-bd {legajo?}` - Generate SICOSS data to database
- `php artisan sicoss:test {legajo}` - Test SICOSS generation for specific employee
- `php artisan concepto-listado:refresh` - Refresh concept listing materialized view
- `php artisan concepto-listado:sync` - Sync concepts from Mapuche system
- `php artisan fallecidos:refresh` - Update deceased employees data from Mapuche
- `php artisan afip:import` - Import AFIP active relations from file
- `php artisan mapuche:generar-sicoss {periodo}` - Generate SICOSS for specific period

### Database Operations
- `php artisan migrate` - Run database migrations
- `php artisan queue:work` - Process background jobs
- `php artisan filament:optimize` - Cache Filament components
- `php artisan optimize` - Optimize Laravel caches

## Database Architecture

### Connection Configuration
Models use different database connections based on data source:
- **Local Models**: Use default `pgsql` connection
- **Mapuche Models**: Use `pgsql-mapuche` connection (namespace `App\Models\Mapuche\`)
- **Repository Pattern**: Implemented for data access abstraction

### Key Model Patterns
```php
// Mapuche models (HR system)
namespace App\Models\Mapuche;
class MapucheEmployee extends Model 
{
    protected $connection = 'pgsql-mapuche';
}

// Local application models
namespace App\Models;
class AfipMapucheSicoss extends Model 
{
    protected $connection = 'pgsql'; // default
}
```

### Critical Tables
- `dh21`, `dh21h` - Payroll concepts (current and historical)
- `dh03` - Employee cargo positions
- `afip_mapuche_sicoss` - AFIP reporting data
- `afip_relaciones_activas` - Active employment relations
- `concepto_listado` - Materialized view for concept listings

## Code Organization

### Services Layer
- `App\Services\Afip\` - AFIP/SICOSS generation services
- `App\Services\Mapuche\` - HR system integration services
- `App\Services\Excel\` - Import/export functionality
- `App\Repositories\` - Data access layer following repository pattern

### Data Transfer Objects
Uses Spatie Laravel Data for DTOs:
- `App\Data\Responses\` - API response DTOs
- `App\Data\Queries\` - Query parameter DTOs
- `App\DTOs\` - General data transfer objects

### Filament Resources
Organized by panel:
- `app/Filament/{PanelName}/Resources/` - Panel-specific resources
- `app/Filament/Resources/` - Shared resources

## Development Guidelines

### Database Queries
- Use repository pattern for complex queries
- Leverage PostgreSQL materialized views for performance
- Implement proper indexing for large datasets
- Use transactions for data integrity operations

### Performance Considerations  
- SICOSS generation involves processing 1.2M+ records
- Use chunked queries and background jobs for large operations
- Implement caching for frequently accessed data
- Monitor query performance with `EXPLAIN ANALYZE`

### Testing
- Run `php artisan test` for PHPUnit tests
- Tests located in `tests/Feature/` and `tests/Unit/`
- Use database transactions in tests for isolation

### Filament Development
- Follow panel-specific organization
- Use Livewire components for dynamic interfaces
- Implement proper authorization using policies
- Leverage Filament's built-in widgets and actions

## Important Notes

- **Character Encoding**: System handles special characters in employee names/locations
- **Multi-tenant**: Database supports multiple organizational units via schemas
- **Background Processing**: Heavy operations use Laravel queues
- **Audit Trail**: Changes tracked through model events and listeners
- **Security**: Implements Laravel Jetstream authentication with team management