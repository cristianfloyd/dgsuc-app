# Checklist de Implementación para Recursos Filament

## Por cada nuevo recurso que requiera tabla

1. **Definición de Tabla**

```php
class {Name}TableDefinition implements AbstractTableDefinitionInterface
{
    public function getColumns(): array
    public function getIndexes(): array 
    public function getTableName(): string
}
```

2. **Servicio de Tabla**

```php
class {Name}TableService extends AbstractTableService
{
    private {Name}TableDefinition $definition;
    
    protected function getTableDefinition(): array
    protected function getIndexes(): array
    protected function getTablePopulationQuery(): string
}
```

3. **Recurso Filament**

```php
class List{Name} extends ListRecords
{
    use FilamentTableInitializationTrait;
    
    protected function getTableServiceClass(): string
    {
        return {Name}TableService::class;
    }
}
```

## Orden de Creación

1. Crear TableDefinition primero (estructura)
2. Implementar TableService (lógica)
3. Integrar en recurso Filament (presentación)

## Comandos Artisan

```bash
# 1. Crear definición
php artisan make:class Contracts/Tables/{Name}TableDefinition

# 2. Crear servicio
php artisan make:class Services/{Name}TableService

# 3. El recurso Filament ya existe, solo agregar el trait
```

## Verificación

* TableDefinition implementa todos los métodos requeridos
* TableService extiende AbstractTableService
* Recurso Filament usa FilamentTableInitializationTrait
* Conexión Mapuche configurada correctamente
