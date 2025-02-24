# Documentación SICOSS Updates

## Descripción General

El módulo de actualización SICOSS es una herramienta que permite actualizar y verificar los datos necesarios para la generación del archivo SICOSS. Este proceso realiza una serie de actualizaciones en las tablas del sistema Mapuche para asegurar la correcta categorización de los agentes.

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

2. **SicossUpdateService**
   ```php
   app/Services/Afip/SicossUpdateService.php
   ```
   - Implementa la lógica de negocio
   - Gestiona transacciones de base de datos
   - Ejecuta actualizaciones en orden específico
   - Realiza verificaciones finales

3. **Vista de Resultados**
   ```php
   resources/views/filament/afip/pages/sicoss-updates.blade.php
   ```
   - Muestra el progreso de la actualización
   - Presenta resultados detallados
   - Visualiza agentes sin código de actividad

## Proceso de Actualización

### Secuencia de Operaciones

1. **Preparación de Tablas Temporales**
   - Drop de tablas existentes
   - Creación de tcargosliq
   - Adición de columnas necesarias

2. **Actualizaciones Secuenciales**
   ```sql
   -- Ejemplo de actualización
   UPDATE tcargosliq SET codsit = 5
   FROM mapuche.dh21
   WHERE mapuche.dh21.nro_liqui = :nro_liqui
   ```

3. **Verificación Final**
   - Identifica agentes sin código de actividad
   - Genera reporte de casos especiales

### Pasos Detallados

1. **Drop Tables** (`dropTemporaryTables`)
   - Elimina tcargosliq y Tcodact si existen

2. **Crear Tabla Temporal** (`createTemporaryTables`)
   - Crea tcargosliq con datos base
   - Parámetros: array de liquidaciones

3. **Alteraciones de Tabla** (`alterTemporaryTables`)
   - Agrega columnas codsit, r21, codact

4. **Updates Secuenciales**
   - Update 1: Actualiza codsit
   - Update 2: Actualiza r21
   - Update 3: Actualiza codact
   - Insert 4: Inserta en dha8
   - Update 5-10: Actualizaciones específicas

5. **Verificación** (`verificarAgentesInactivos`)
   - Crea tabla temporal aaa
   - Identifica agentes sin actividad

## Uso del Sistema

### Para Usuarios

1. Acceder al panel AFIP
2. Navegar a "Actualización SICOSS"
3. Hacer clic en "Ejecutar Actualizaciones"
4. Revisar resultados y verificaciones

### Para Desarrolladores

1. **Configuración de Conexión**
   ```php
   use App\Traits\MapucheConnectionTrait;
   ```

2. **Manejo de Liquidaciones**
   ```php
   public function executeUpdates(array $liquidaciones = [6]): array
   ```

3. **Extensión del Sistema**
   - Agregar nuevos métodos en SicossUpdateService
   - Actualizar la vista para nuevos resultados
   - Mantener la estructura de transacciones

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
- Resultados detallados por operación
- Tabla de agentes sin código de actividad
- Notificaciones de éxito/error

## Consideraciones Técnicas

### Base de Datos
- Conexión específica a Mapuche
- Uso de transacciones para integridad
- Tablas temporales para procesamiento

### Seguridad
- Validación de liquidaciones
- Control de acceso mediante panel
- Manejo de errores y rollback

### Performance
- Optimización de consultas
- Uso eficiente de tablas temporales
- Procesamiento por lotes cuando es posible

## Mantenimiento

### Logs y Debugging
```php
Log::error('Error en actualización SICOSS: ' . $e->getMessage());
```

### Actualizaciones Futuras
1. Agregar nuevos tipos de verificación
2. Expandir reportes de resultados
3. Implementar exportación de resultados

### Problemas Comunes
- Verificar permisos de base de datos
- Confirmar existencia de liquidaciones
- Validar integridad de datos fuente 
