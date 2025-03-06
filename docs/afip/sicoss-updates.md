# Documentación SICOSS Updates

## Descripción General

El módulo de actualización SICOSS es una herramienta que permite actualizar y verificar los datos necesarios para la generación del archivo SICOSS. Este proceso realiza una serie de actualizaciones en las tablas del sistema Mapuche para asegurar la correcta categorización de los agentes. El sistema permite seleccionar múltiples liquidaciones para procesar y determina automáticamente si debe consultar tablas actuales o históricas según el período fiscal.

## Estructura del Sistema

### Panel AFIP

- **Ruta**: `/afip-panel/sicoss-updates`
- **Navegación**: AFIP > Actualización SICOSS
- **Permisos requeridos**: Acceso al panel AFIP

### Componentes Principales

1. **SicossUpdates (Page)**

   ```php
   app/Filament/Afip/Pages/SicossUpdates.php
   ```

   - Maneja la interfaz de usuario
   - Proporciona el botón de ejecución
   - Muestra resultados y feedback
   - Gestiona estados de procesamiento
   - Integra el widget de selección de liquidaciones

2. **MultipleIdLiquiSelector (Widget)**

   ```php
   app/Filament/Widgets/MultipleIdLiquiSelector.php
   ```

   - Permite seleccionar múltiples liquidaciones
   - Comunica las selecciones a la página principal
   - Reacciona a cambios en el periodo fiscal
   - Filtra liquidaciones según criterios

3. **SicossUpdateService**

   ```php
   app/Services/Afip/SicossUpdateService.php
   ```

   - Implementa la lógica de negocio
   - Gestiona transacciones de base de datos
   - Ejecuta actualizaciones en orden específico
   - Procesa las liquidaciones seleccionadas
   - Realiza verificaciones finales
   - Determina dinámicamente qué tablas consultar (actuales o históricas)

4. **TableSelectorService**

   ```php
   app/Services/Mapuche/TableSelectorService.php
   ```

   - Determina si usar tablas actuales o históricas según el período fiscal
   - Compara el período de la liquidación con el período actual
   - Proporciona métodos para reemplazar nombres de tablas en consultas SQL
   - Maneja casos de error y fallback a tablas por defecto

5. **PeriodoFiscalService**

   ```php
   app/Services/Mapuche/PeriodoFiscalService.php
   ```

   - Gestiona información sobre períodos fiscales
   - Obtiene el período fiscal actual de la base de datos
   - Compara períodos fiscales para determinar si son actuales o históricos

6. **Vista de Resultados**

   ```php
   resources/views/filament/afip/pages/sicoss-updates.blade.php
   ```

   - Muestra el progreso de la actualización
   - Presenta resultados detallados
   - Visualiza agentes sin código de actividad
   - Muestra las liquidaciones seleccionadas

## Selección de Tablas Dinámicas

### Lógica de Selección de Tablas

El sistema determina automáticamente si debe consultar la tabla actual (`dh21`) o la tabla histórica (`dh21h`) basándose en el período fiscal de la liquidación seleccionada:

1. **Tabla Actual (dh21)**:
   - Se utiliza cuando el período fiscal de la liquidación es igual o posterior al período fiscal actual en la base de datos.

2. **Tabla Histórica (dh21h)**:
   - Se utiliza cuando el período fiscal de la liquidación es anterior al período fiscal actual en la base de datos.

### Implementación

```php
// En TableSelectorService.php
public function getDh21TableName($liquidacion): string
{
    // Si es un array, tomamos la primera liquidación para determinar el período
    $nroLiqui = is_array($liquidacion) ? $liquidacion[0] : $liquidacion;
    
    // Obtener el período fiscal de la liquidación
    $liquidacionModel = Dh22::where('nro_liqui', $nroLiqui)->first();
    
    // Obtener el período fiscal actual de la base de datos
    $periodoActual = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();
    
    // Comparar períodos fiscales
    if ($yearLiquidacion < $yearActual || 
        ($yearLiquidacion == $yearActual && $mesLiquidacion < $mesActual)) {
        return 'dh21h'; // Tabla histórica
    }
    
    return 'dh21'; // Tabla actual
}
```

### Uso en Consultas SQL

Las consultas SQL utilizan reemplazo de placeholders para insertar el nombre de tabla correcto:

```php
// En SicossUpdateService.php
$dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

$query = "UPDATE tcargosliq SET codsit = 5
        FROM mapuche.{$dh21Table}
        WHERE mapuche.{$dh21Table}.nro_liqui IN ($placeholders)
        AND tcargosliq.nro_cargo = mapuche.{$dh21Table}.nro_cargo
        AND mapuche.{$dh21Table}.codn_conce = '126'";
```

## Selección de Liquidaciones

### Widget MultipleIdLiquiSelector

El widget permite a los usuarios seleccionar múltiples liquidaciones para procesar:

- **Ubicación**: Encabezado de la página SicossUpdates
- **Funcionalidad**:
  - Selección múltiple de liquidaciones
  - Filtrado por periodo fiscal
  - Comunicación reactiva con la página principal
  - Validación de selecciones

### Integración con SicossUpdates

```php
// En SicossUpdates.php
protected function getHeaderWidgets(): array
{
    return [
        MultipleIdLiquiSelector::class,
    ];
}

#[On('idsLiquiSelected')]
public function handleIdsLiquiSelected($idsLiqui): void
{
    $this->selectedIdLiqui = $idsLiqui;
}
```

### Flujo de Datos

1. Usuario selecciona liquidaciones en el widget
2. El widget emite evento `idsLiquiSelected`
3. La página SicossUpdates captura el evento y actualiza `$selectedIdLiqui`
4. El botón de ejecución se habilita solo cuando hay liquidaciones seleccionadas
5. Al ejecutar, las liquidaciones seleccionadas se pasan al servicio
6. El servicio determina qué tablas consultar según el período fiscal de las liquidaciones

## Proceso de Actualización

### Secuencia de Operaciones

1. **Selección de Liquidaciones**
   - Selección múltiple desde el widget
   - Validación de selecciones

2. **Determinación de Tablas**
   - Análisis del período fiscal de las liquidaciones
   - Selección entre tablas actuales o históricas

3. **Preparación de Tablas Temporales**
   - Drop de tablas existentes
   - Creación de tcargosliq
   - Adición de columnas necesarias

4. **Actualizaciones Secuenciales**

   ```sql
   -- Ejemplo de actualización con tabla dinámica
   UPDATE tcargosliq SET codsit = 5
   FROM mapuche.{TABLE}
   WHERE mapuche.{TABLE}.nro_liqui = :nro_liqui
   ```

5. **Verificación Final**
   - Identifica agentes sin código de actividad
   - Genera reporte de casos especiales

### Pasos Detallados

1. **Selección de Liquidaciones**
   - Utiliza el widget MultipleIdLiquiSelector
   - Permite filtrar por periodo fiscal
   - Valida que al menos una liquidación esté seleccionada

2. **Determinación de Tablas** (Nuevo)
   - Obtiene el período fiscal de las liquidaciones seleccionadas
   - Compara con el período fiscal actual en la base de datos
   - Selecciona `dh21` o `dh21h` según corresponda

3. **Drop Tables** (`dropTemporaryTables`)
   - Elimina tcargosliq y Tcodact si existen

4. **Crear Tabla Temporal** (`createTemporaryTables`)
   - Crea tcargosliq con datos base
   - Parámetros: array de liquidaciones seleccionadas
   - Utiliza la tabla correcta según el período fiscal

5. **Updates Secuenciales**
   - Update 1: Actualiza codsit (usando tabla dinámica)
   - Update 2: Actualiza r21
   - Update 3: Actualiza codact (usando tabla dinámica)
   - Insert 4: Inserta en dha8 (usando tabla dinámica)
   - Update 5-10: Actualizaciones específicas

6. **Verificación** (`verificarAgentesInactivos`)
   - Crea tabla temporal aaa (usando tabla dinámica)
   - Identifica agentes sin actividad

## Uso del Sistema

### Para Usuarios

1. Acceder al panel AFIP
2. Navegar a "Actualización SICOSS"
3. **Seleccionar una o más liquidaciones** usando el widget
4. Hacer clic en "Ejecutar Actualizaciones"
5. Revisar resultados y verificaciones

### Para Desarrolladores

1. **Configuración de Conexión**

   ```php
   use App\Traits\MapucheConnectionTrait;
   ```

2. **Manejo de Liquidaciones y Tablas**

   ```php
   // Actualizado para determinar dinámicamente qué tabla usar
   public function executeUpdates(?array $liquidaciones = null): array
   {
       // Si no se proporcionan liquidaciones, usar la liquidación 6 por defecto
       $liquidaciones = $liquidaciones ?: [6];
       
       // Determinar qué tabla usar según el período fiscal
       $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);
       
       // Resto del código...
   }
   ```

3. **Integración del Widget**

   ```php
   // En SicossUpdates.php
   use App\Filament\Widgets\MultipleIdLiquiSelector;
   
   // Definir propiedad para almacenar selecciones
   public ?array $selectedIdLiqui = null;
   
   // Agregar widget al encabezado
   protected function getHeaderWidgets(): array
   {
       return [
           MultipleIdLiquiSelector::class,
       ];
   }
   ```

4. **Uso del TableSelectorService**

   ```php
   // Inyección de dependencias
   public function __construct(TableSelectorService $tableSelectorService)
   {
       $this->tableSelectorService = $tableSelectorService;
   }
   
   // Uso en métodos
   $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);
   $query = str_replace('{TABLE}', "mapuche.{$dh21Table}", $queryTemplate);
   ```

5. **Extensión del Sistema**
   - Agregar nuevos métodos en SicossUpdateService
   - Actualizar la vista para nuevos resultados
   - Mantener la estructura de transacciones
   - Extender el widget para nuevas funcionalidades
   - Ampliar la lógica de selección de tablas para otros casos

## Resultados y Feedback

### Estructura de Resultados

```php
[
    'status' => 'success|error',
    'message' => 'Mensaje descriptivo',
    'update_X_name' => [
        'status' => 'success',
        'rows_affected' => n
    ],
    'verificacion_agentes' => [
        'status' => 'success',
        'agentes_sin_actividad' => [],
        'total_agentes' => n
    ]
]
```

### Visualización

- Indicador de progreso durante el proceso
- **Listado de liquidaciones seleccionadas**
- Resultados detallados por operación
- Tabla de agentes sin código de actividad
- Notificaciones de éxito/error
- **Mensaje de validación cuando no hay liquidaciones seleccionadas**

## Consideraciones Técnicas

### Base de Datos

- Conexión específica a Mapuche
- Uso de transacciones para integridad
- Tablas temporales para procesamiento
- **Filtrado por liquidaciones seleccionadas**
- **Selección dinámica entre tablas actuales e históricas**
- **Manejo de períodos fiscales para determinar la tabla correcta**

### Seguridad

- Validación de liquidaciones
- Control de acceso mediante panel
- Manejo de errores y rollback
- **Validación de selecciones de usuario**
- **Logging de decisiones sobre tablas utilizadas**

### Performance

- Optimización de consultas
- Uso eficiente de tablas temporales
- Procesamiento por lotes cuando es posible
- **Procesamiento selectivo por liquidaciones**
- **Acceso a tablas históricas solo cuando es necesario**

### Comunicación entre Componentes

- **Uso de eventos Livewire para comunicación reactiva**
- **Actualización de estado en tiempo real**
- **Validación de formularios antes de procesamiento**
- **Inyección de dependencias para servicios**

## Mantenimiento

### Logs y Debugging

```php
Log::error('Error en actualización SICOSS: ' . $e->getMessage());
Log::info("Usando tabla histórica dh21h para liquidación {$nroLiqui} del período {$yearLiquidacion}-{$mesLiquidacion}");
```

### Actualizaciones Futuras

1. Agregar nuevos tipos de verificación
2. Expandir reportes de resultados
3. Implementar exportación de resultados
4. **Mejorar filtros de selección de liquidaciones**
5. **Agregar previsualización de datos antes de procesar**
6. **Extender la lógica de selección de tablas a otras entidades**
7. **Implementar caché de decisiones sobre tablas**

### Problemas Comunes

- Verificar permisos de base de datos
- Confirmar existencia de liquidaciones
- Validar integridad de datos fuente
- **Asegurar que el widget cargue correctamente las opciones**
- **Verificar la comunicación entre widget y página principal**
- **Comprobar que existan las tablas históricas necesarias**
- **Validar que la estructura de tablas históricas sea compatible**
