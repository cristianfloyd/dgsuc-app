# Recursos Personalizados en Filament

Este documento contiene un índice de los recursos personalizados implementados en la aplicación utilizando Filament, con enfoque en patrones avanzados o personalizaciones específicas.

## Índice de Recursos Personalizados

| Recurso | Panel | Descripción | Patrón/Técnica | Documentación |
|---------|-------|-------------|----------------|---------------|
| Licencias Vigentes | Reportes | Consulta de licencias vigentes en el periodo fiscal | DTO + Service | [Ver documentación](licencias-vigentes.md) |
| Bloqueos | Reportes | Gestión de bloqueos de agentes | Resource estándar + acciones personalizadas | [Ver documentación](../documentacion_bloqueos_resource.md) |

## Patrones Implementados

### Patrón DTO + Service Layer

Este patrón es utilizado cuando se necesita:

1. **Trabajar con datos complejos** que no corresponden directamente a un modelo Eloquent
2. **Abstraer lógica compleja de consulta** hacia una capa de servicio
3. **Mapear resultados** de bases de datos externas o consultas complejas
4. **Preparar datos para exportación** en formatos específicos

#### Recursos que utilizan este patrón

- [Licencias Vigentes](licencias-vigentes.md)

### Recursos sin Modelo Eloquent

Algunos recursos en Filament no están directamente vinculados a un modelo Eloquent, sino que utilizan otros mecanismos para obtener y mostrar datos.

#### Técnicas utilizadas

- Uso de `protected static ?string $model = null;` para indicar que no hay modelo asociado
- Sobrescritura del método `getTableRecords()` para proporcionar datos desde otras fuentes
- Conversión de colecciones regulares a `EloquentCollection` para compatibilidad con Filament

#### Solución a errores comunes

Cuando se implementa un recurso sin modelo Eloquent, pueden surgir errores como:

```php
Class "App\Models\MiRecurso" not found
```

Para evitar estos errores, es necesario sobrescribir dos métodos adicionales:

1. En el Recurso:

```php
public static function getEloquentQuery(): Builder
{
    // Devolvemos una query vacía que no afecte el funcionamiento
    return DB::table('users')->whereRaw('1=0')->toBase();
}
```

2. En la página ListRecords:

```php
protected function getTableQuery(): Builder
{
    // También devolvemos una query vacía
    return DB::table('users')->whereRaw('1=0')->toBase();
}
```

Estos métodos son necesarios porque Filament intenta acceder a ellos en su flujo de trabajo interno, incluso cuando no utilizamos un modelo real.

#### Manejo de valores nulos en callbacks

Al trabajar con recursos sin modelo Eloquent es importante tener en cuenta que Filament evalúa algunas callbacks (como las de visibilidad o formato) antes de que los datos estén disponibles. Es necesario implementar verificaciones para evitar errores:

```php
// ❌ Incorrecto: No maneja el caso donde $record es null
TextColumn::make('campo')
    ->visible(fn (MiDTO $record): bool => $record->alguna_propiedad)

// ✅ Correcto: Verifica el tipo antes de acceder a propiedades
TextColumn::make('campo')
    ->visible(fn ($record): bool => $record instanceof MiDTO && $record->alguna_propiedad)
```

Este patrón debe aplicarse en:
- Callbacks de visibilidad (`->visible()`)
- Callbacks de formato (`->formatStateUsing()`)
- Callbacks de color (`->color()`)
- Callbacks de filtros (`->query()`)

#### Recursos que utilizan esta técnica

- [Licencias Vigentes](licencias-vigentes.md)

## Cómo crear nuevos recursos personalizados

### Usando Artisan y personalizando

```bash
# 1. Generar el recurso base con scaffolding
php artisan make:filament-resource NombreRecurso --generate --panel=NombrePanel

# 2. Personalizar según sea necesario
# - Modificar el modelo o establecerlo como null
# - Sobrescribir métodos como getTableRecords()
# - Personalizar acciones y filtros
```

### Recomendaciones

- Usar DTOs para transferir datos cuando la estructura es compleja
- Utilizar servicios para encapsular lógica de negocio reutilizable
- Aprovechar las capacidades de filtrado y exportación de Filament
- Documentar adecuadamente los recursos personalizados siguiendo la estructura de esta documentación
- Siempre verificar el tipo de los registros en las callbacks cuando se trabaja sin modelo Eloquent
