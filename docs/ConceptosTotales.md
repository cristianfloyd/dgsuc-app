# Implementación del Repositorio de Conceptos Totales

## Resumen

Este documento describe la implementación de un sistema para obtener totales agregados por concepto desde la base de datos Mapuche, transformando una consulta SQL compleja en un patrón Repository/Service con tipado estricto y compatibilidad con Laravel 11.

## Estructura y Componentes

La solución implementa un patrón de diseño en capas con las siguientes partes:

### 1. Capa de Datos (Repository)

**ConceptosTotalesRepositoryInterface**
- Interfaz que define los contratos para obtener datos agregados
- Facilita los tests unitarios mediante mocks
- Ubicación: `app/Repositories/Interfaces/ConceptosTotalesRepositoryInterface.php`

```php
interface ConceptosTotalesRepositoryInterface
{
    public function getTotalesPorConcepto(array $conceptos, int $year, int $month): Collection;
    public function getTotalesPorConceptoAgrupados(array $conceptos, int $year, int $month): array;
}
```

**ConceptosTotalesRepository**
- Implementación concreta del repositorio
- Utiliza `MapucheConnectionTrait` para gestionar la conexión a la base de datos
- Ejecuta la consulta original utilizando el Query Builder de Laravel
- Ubicación: `app/Repositories/Mapuche/ConceptosTotalesRepository.php`

```php
class ConceptosTotalesRepository implements ConceptosTotalesRepositoryInterface
{
    use MapucheConnectionTrait;

    public function getTotalesPorConcepto(array $conceptos, int $year, int $month): Collection
    {
        $connection = DB::connection($this->getConnectionName());

        return $connection->table(function ($query) {
            $query->from('mapuche.dh21')
                ->select('*')
                ->unionAll(
                    DB::table('mapuche.dh21h')->select('*')
                );
        }, 'h21')
        ->join('mapuche.dh12 as h12', 'h21.codn_conce', '=', 'h12.codn_conce')
        ->whereIn('h21.codn_conce', $conceptos)
        ->whereIn('h21.nro_liqui', function ($sub) use ($year, $month) {
            $sub->from('mapuche.dh22')
                ->select('nro_liqui')
                ->where('sino_genimp', true)
                ->where('per_liano', $year)
                ->where('per_limes', $month);
        })
        ->groupBy('h21.codn_conce', 'h12.desc_conce')
        ->orderBy('h21.codn_conce')
        ->select([
            'h21.codn_conce',
            'h12.desc_conce',
            DB::raw('SUM(impp_conce)::numeric(15,2) as importe')
        ])
        ->get();
    }

    public function getTotalesPorConceptoAgrupados(array $conceptos, int $year, int $month): array
    {
        // Implementación de agrupación por tipo de concepto (haberes/descuentos)
        // ...
    }
}
```

### 2. Objetos de Transferencia de Datos (DTOs)

**ConceptoTotalItemData**
- Representa un concepto individual con su importe
- Facilita el tipado estricto y la transformación de datos
- Ubicación: `app/Data/Responses/ConceptoTotalItemData.php`

```php
class ConceptoTotalItemData extends Data
{
    public function __construct(
        #[MapName('codn_conce')]
        public readonly string $codigoConcepto,
        
        #[MapName('desc_conce')]
        public readonly string $descripcionConcepto,
        
        #[MapName('importe')]
        public readonly float $importe
    ) {}

    // Métodos de transformación
    // ...
}
```

**ConceptoTotalAgrupacionData**
- Representa el reporte completo con haberes, descuentos y totales
- Ubicación: `app/Data/Responses/ConceptoTotalAgrupacionData.php`

```php
class ConceptoTotalAgrupacionData extends Data
{
    public function __construct(
        #[MapName('haberes')]
        public readonly Collection $haberes,
        
        #[MapName('descuentos')]
        public readonly Collection $descuentos,
        
        #[MapName('total_haberes')]
        public readonly float $totalHaberes,
        
        #[MapName('total_descuentos')]
        public readonly float $totalDescuentos,
        
        #[MapName('neto')]
        public readonly float $neto
    ) {}

    // Métodos de transformación y exportación
    // ...
}
```

### 3. Capa de Servicio (Service)

**ConceptosTotalesService**
- Orquesta la lógica de negocio utilizando el repositorio
- Transforma los datos crudos en DTOs bien tipados
- Proporciona métodos convenientes con valores predeterminados
- Ubicación: `app/Services/Reportes/ConceptosTotalesService.php`

```php
class ConceptosTotalesService
{
    public function __construct(
        private readonly ConceptosTotalesRepositoryInterface $repository
    ) {}

    public function getTotalesPorConcepto(array $conceptos, int $year, int $month): Collection
    {
        // Transforma resultados a DTOs
        // ...
    }

    public function getTotalesAgrupados(array $conceptos, int $year, int $month): ConceptoTotalAgrupacionData
    {
        // Obtiene totales agrupados
        // ...
    }

    public function getReporteConceptos(int $year, int $month, ?array $conceptos = null): ConceptoTotalAgrupacionData
    {
        // Método conveniente con valores predeterminados
        // ...
    }
}
```

### 4. Configuración de Dependencias

**AppServiceProvider**
- Registra la vinculación entre la interfaz y la implementación
- Ubicación: `app/Providers/AppServiceProvider.php`

```php
public function register(): void
{
    // Otras vinculaciones...
    
    $this->app->bind(
        ConceptosTotalesRepositoryInterface::class,
        ConceptosTotalesRepository::class
    );
}
```

## Detalles técnicos

### Transformación de la consulta SQL original

La consulta SQL original:
```sql
SELECT
    h21.codn_conce,
    h12.desc_conce,
    SUM(impp_conce)::numeric(15, 2) as importe
FROM (
    SELECT * FROM mapuche.dh21
    UNION ALL
    SELECT * FROM mapuche.dh21h
) h21
JOIN mapuche.dh12 AS h12 ON h21.codn_conce = h12.codn_conce
WHERE h21.codn_conce IN ('201', '202', ...) 
AND h21.nro_liqui IN (
    SELECT d22.nro_liqui
    FROM mapuche.dh22 AS d22
    WHERE d22.sino_genimp = true
    AND d22.per_liano = 2025
    AND d22.per_limes = 03
)
GROUP BY h21.codn_conce, h12.desc_conce
ORDER BY h21.codn_conce;
```

Ha sido transformada a un Query Builder de Laravel manteniendo exactamente la misma lógica y estructura, pero aprovechando las ventajas del ORM.

### Lógica de agrupación por tipo de concepto

La implementación incluye una lógica para clasificar automáticamente los conceptos en haberes o descuentos basándose en el primer dígito del código:

- Haberes: códigos que comienzan con 2, 4, 6, 8
- Descuentos: códigos que comienzan con 3, 5, 7, 9

### Conexión a la base de datos

Se utiliza el trait `MapucheConnectionTrait` para gestionar la conexión a la base de datos Mapuche, manteniendo la separación de responsabilidades y siguiendo las convenciones del proyecto.

## Ejemplo de uso

### En un controlador

```php
class EjemploController extends Controller
{
    public function __construct(
        private readonly ConceptosTotalesService $conceptosTotalesService
    ) {}

    public function obtenerReporte(Request $request)
    {
        // Validar parámetros
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        // Obtener el reporte
        $reporte = $this->conceptosTotalesService->getReporteConceptos(
            $request->input('year'),
            $request->input('month')
        );

        // Usar los datos
        return view('reportes.conceptos', [
            'reporte' => $reporte,
            'haberes' => $reporte->haberes,
            'descuentos' => $reporte->descuentos,
            'totalHaberes' => $reporte->totalHaberes,
            'totalDescuentos' => $reporte->totalDescuentos,
            'neto' => $reporte->neto
        ]);
    }
}
```

### En un recurso Filament

```php
public function getTableContent(): array
{
    $year = $this->year;
    $month = $this->month;
    
    $conceptosTotalesService = app(ConceptosTotalesService::class);
    $reporte = $conceptosTotalesService->getReporteConceptos($year, $month);
    
    return [
        // Configuración de tablas, charts, etc. usando los datos del reporte
    ];
}
```

## Ventajas del enfoque

1. **Mantenibilidad**: Separa responsabilidades en capas bien definidas
2. **Testabilidad**: Facilita las pruebas unitarias mediante interfaces
3. **Tipado estricto**: Usa DTOs para garantizar la estructura de datos
4. **Reusabilidad**: Permite reutilizar la lógica en diferentes contextos
5. **Coherencia con el proyecto**: Sigue las convenciones y patrones existentes
6. **Optimización de consulta**: Mantiene la eficiencia de la consulta SQL original

## Conclusión

Esta implementación transforma una consulta SQL directa en una arquitectura robusta y mantenible, siguiendo las mejores prácticas de Laravel 11 y el patrón Repository/Service. La solución aprovecha los beneficios del tipado estricto, la inyección de dependencias y la separación de responsabilidades, facilitando futuras extensiones y mantenimiento. 
