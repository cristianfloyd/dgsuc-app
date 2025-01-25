# Guía de Arquitectura para Servicios de Tabla

## Índice

1. [Inicio Rápido](#inicio-rápido)
2. [Visión General](#visión-general)
3. [Estructura de Directorios](#estructura-de-directorios)
4. [Componentes Principales](#componentes-principales)
5. [Flujo de Trabajo](#flujo-de-trabajo)
6. [Ejemplos de Implementación](#ejemplos-de-implementación)
7. [Testing](#testing)
8. [Solución de Problemas](#solución-de-problemas)
9. [Mejores Prácticas](#mejores-prácticas)

## Inicio Rápido

### Prerequisitos

- PHP 8.1+
- Laravel 11+
- PostgreSQL 13+
- Filament 3+

### Instalación

```bash
# 1. Crear estructura base
php artisan make:class Contracts/TableService/TableServiceInterface
php artisan make:class Services/Abstract/AbstractTableService
php artisan make:class Services/TableManager/TableInitializationManager
php artisan make:trait FilamentTableInitializationTrait

# 2. Configurar conexión Mapuche
# Editar config/database.php
```

### Primer Servicio de Tabla

1. Crear TableDefinition
2. Implementar TableService
3. Integrar con Filament

## Visión General

Esta arquitectura proporciona una estructura robusta para manejar la creación, verificación y población de tablas en la base de datos, siguiendo principios SOLID y patrones de diseño.

### Objetivos

- Código mantenible y reutilizable
- Separación clara de responsabilidades
- Testing sencillo
- Manejo consistente de errores
- Documentación clara

## Estructura de Directorios

```ascii
app/
├── Contracts/
│   ├── TableService/
│   │   └── TableServiceInterface.php
│   └── Tables/
│       ├── AbstractTableDefinitionInterface.php
│       └── {TableName}TableDefinition.php
├── Services/
│   ├── Abstract/
│   │   └── AbstractTableService.php
│   ├── TableManager/
│   │   └── TableInitializationManager.php
│   └── {TableName}TableService.php
└── Traits/
    ├── FilamentTableInitializationTrait.php
    └── MapucheConnectionTrait.php
```

## Componentes Principales

### 1. Interfaces (Contracts)

#### TableServiceInterface

- Define el contrato base para todos los servicios de tabla:

```php
interface TableServiceInterface 
{
    public function exists(): bool;
    public function createAndPopulate(): void;
    public function getTableName(): string;
}
```

#### AbstractTableDefinitionInterface

- Define la estructura de las definiciones de tabla:

```php
interface AbstractTableDefinitionInterface
{
    public function getTableName(): string;
    public function getColumns(): array;
    public function getIndexes(): array;
}
```

### 2. Servicios Base

#### AbstractTableService

##### Implementa la lógica común para todos los servicios de tabla

- Manejo de conexiones
- Creación de tablas
- Gestión de índices
- Población de datos
- Manejo de errores

### 3. Managers

#### TableInitializationManager

- Coordina la inicialización de tablas:
- Verifica existencia
- Maneja errores
- Registra eventos

### 4. Traits

#### FilamentTableInitializationTrait

- Integra la inicialización de tablas en recursos Filament:

## Flujo de Trabajo

### Definición de Tabla

#### Definicion de Tabla

```php
class MiTablaDefinition implements AbstractTableDefinitionInterface
{
    public function getColumns(): array
    {
        return [
            'id' => ['type' => 'bigIncrements'],
            // ...
        ];
    }
}
```

### Servicio de Tabla

```php
    class MiTablaService extends AbstractTableService
    {
        private MiTablaDefinition $definition;

        public function getTableDefinition(): array
        {
            return $this->definition->getColumns();
        }
    }
```

### Recurso Filament

```php
    class ListMiTabla extends ListRecords
    {
        use FilamentTableInitializationTrait;

        protected function getTableServiceClass(): string
        {
            return MiTablaService::class;
        }
    }
```

## **Flujo de Trabajo**

```graph TD
    A[TableDefinition] --> B[TableService]
    B --> C[TableInitializationManager]
    C --> D[FilamentResource]
    E[MapucheConnectionTrait] --> B
```

## Ejemplos de Implementación

### Crear Nueva Tabla

1. Crear Definición:

```php
php artisan make:class Contracts/Tables/NuevaTablaDefinition
```

2. Crear Servicio:

```php
php artisan make:class Services/NuevaTablaService
```

3. Implementar en Filament:

```php
use FilamentTableInitializationTrait;
```

## Mejores Prácticas

### 1. Convenciones de Nombres

1. Sufijos: TableDefinition, TableService
2. Prefijos: get, create, populate
3. Namespaces: App\Contracts\Tables, App\Services

### 2. Estructura de Archivos

```ascii
app/
├── Contracts/
│   ├── TableService/
│   └── Tables/
├── Services/
│   ├── Abstract/
│   └── TableManager/
└── Traits/

```

### 2. Patrones Recomendados

1. Repository Pattern para consultas complejas
2. Factory Pattern para creación de servicios
3. Strategy Pattern para diferentes tipos de población

### 3. Optimizaciones

1. Índices estratégicos
2. Consultas eficientes
3. Transacciones apropiadas

## Recursos y Referencias

### Documentación Oficial

- Laravel Documentation
- Filament Documentation
- PostgreSQL Documentation

### Herramientas Recomendadas

1. Laravel Telescope para debugging
2. pgAdmin y datagrip para gestión de BD
3. PHPUnit para testing

### Ejemplos y Plantillas

```php

````

## Conclusión

### Esta arquitectura proporciona

- Estructura clara y mantenible
- Separación de responsabilidades
- Facilidad de testing
- Documentación comprensible
- Base sólida para crecimiento

## Patrones y Principios SOLID Aplicados

### Single Responsibility Principle (SRP)

Cada clase tiene una única responsabilidad:

- `TableDefinition`: Define estructura
- `TableService`: Maneja operaciones
- `TableInitializationManager`: Coordina inicialización

### Open/Closed Principle (OCP)

#### La arquitectura permite extensión sin modificación

```php
// Nuevo tipo de tabla
class NuevaTablaService extends AbstractTableService 
{
    protected function getTableDefinition(): array 
    {
        return $this->definition->getColumns();
    }
}
```

### Interface Segregation Principle (ISP)

#### Interfaces pequeñas y específicas

```php
interface TableServiceInterface 
{
    public function exists(): bool;
    public function createAndPopulate(): void;
    public function getTableName(): string;
}
```

## Integración con Base de Datos Mapuche

### Configuración de Conexión

```php
// config/database.php
'connections' => [
    'pgsql-mapuche' => [
        'driver' => 'pgsql',
        'host' => env('DB_MAPUCHE_HOST'),
        'database' => env('DB_MAPUCHE_DATABASE'),
        // ...
    ],
]
```

### Uso del MapucheConnectionTrait

```php
trait MapucheConnectionTrait
{
    public function getConnectionName(): string
    {
        return 'pgsql-mapuche';
    }

    public function getConnectionFromTrait()
    {
        return DB::connection($this->getConnectionName());
    }
}
```

## Casos de Uso Comunes

### 1. Tabla con Relaciones

```php
class TablaConRelacionesDefinition implements AbstractTableDefinitionInterface
{
    public function getColumns(): array
    {
        return [
            'id' => ['type' => 'bigIncrements'],
            'tabla_relacionada_id' => [
                'type' => 'bigInteger',
                'foreign' => 'tabla_relacionada.id'
            ]
        ];
    }

    public function getIndexes(): array
    {
        return [
            'fk_tabla_relacionada' => ['tabla_relacionada_id']
        ];
    }
}
```

### 2. Tabla Temporal

```php
class TablaTempService extends AbstractTableService
{
    protected function createTable(): void
    {
        $sql = "CREATE TEMP TABLE IF NOT EXISTS {$this->getTableName()} (...)";
        $this->getConnectionFromTrait()->statement($sql);
    }
}

```

### 3. Tabla con Triggers

```php
class TablaConTriggersService extends AbstractTableService
{
    protected function afterPopulate(): void
    {
        $this->createTriggers();
    }

    private function createTriggers(): void
    {
        $sql = "CREATE TRIGGER ...";
        $this->getConnectionFromTrait()->statement($sql);
    }
}
```

## Buenas Prácticas de Logging

### 1. Logging Estructurado

```php
Log::info('Inicializando tabla', [
    'tabla' => $this->getTableName(),
    'conexion' => $this->getConnectionName(),
    'timestamp' => now()
]);
```

### 2. Manejo de Errores

```php
try {
    // Operación
} catch (\Exception $e) {
    Log::error('Error en operación de tabla', [
        'tabla' => $this->getTableName(),
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    throw $e;
}
```

## Testing

### 1. Unit Tests

```php
class TableServiceTest extends TestCase
{
    public function test_table_creation()
    {
        $service = new MiTablaService(new MiTablaDefinition());
        $this->assertFalse($service->exists());
        $service->createAndPopulate();
        $this->assertTrue($service->exists());
    }
}

class TableDefinitionTest extends TestCase
{
    /** @test */
    public function it_provides_correct_table_structure()
    {
        $definition = new MiTablaDefinition();
        $columns = $definition->getColumns();
        
        $this->assertArrayHasKey('id', $columns);
        $this->assertEquals('bigIncrements', $columns['id']['type']);
    }
}
```

### 2. Feature Tests

````php
class TableServiceFeatureTest extends TestCase
{
    /** @test */
    public function it_creates_and_populates_table()
    {
        $service = app(MiTablaService::class);
        $service->createAndPopulate();
        
        $this->assertDatabaseHas($service->getTableName(), [
            // Datos esperados
        ]);
    }
}
````

### 3. Integration Tests

```php
class TableIntegrationTest extends TestCase
{
    public function test_table_population()
    {
        $manager = app(TableInitializationManager::class);
        $service = app(MiTablaService::class);
        
        $result = $manager->initializeTable($service);
        $this->assertTrue($result);
        
        $this->assertDatabaseHas($service->getTableName(), [
            // datos esperados
        ]);
    }
}
```

## Solución de Problemas

### Errores de Conexión

1. Verificar credenciales en .env
2. Comprobar permisos de PostgreSQL
3. Validar configuración de red

### Problemas de Índices

1. Verificar nombres duplicados
2. Comprobar límites de longitud
3. Validar tipos de datos

### Errores de Población

1. Verificar consulta SQL
2. Comprobar transformaciones de datos
3. Validar restricciones de FK

## Mantenimiento y Evolución

### Versionado de Estructuras

- Mantener historial de cambios en definiciones
- Documentar modificaciones importantes
- Considerar impacto en datos existentes

### Monitoreo

- Registrar métricas de rendimiento
- Monitorear uso de recursos
- Alertar sobre errores críticos

### Optimización

- Revisar índices periódicamente
- Analizar consultas frecuentes
- Optimizar estructuras según uso
