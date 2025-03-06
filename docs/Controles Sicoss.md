# Controles Sicoss

## 1. Controles principales

(El trait SicossConnectionTrait establece la conexión de la base de datos)

1. Control de Aportes y Contribuciones
    - Compara los aportes y contribuciones calculados en DH21 vs los registrados en SICOSS
    - Incluye:
        - Aportes SIJP (códigos 201, 202, 203, 205, 204)
        - Aportes INSSJP (código 247)
        - Contribuciones SIJP (códigos 301, 302, 303, 304, 307)
        - Contribuciones INSSJP (código 347)
    - Verifica diferencias por CUIL
    - Agrupa diferencias por dependencia y carácter
    - Almacena resultados en tabla `control_aportes_diferencias`
    - Campos almacenados:
        - Aportes DH21 (SIJP e INSSJP)
        - Aportes SICOSS (SIJP e INSSJP)
        - Contribuciones DH21 (SIJP e INSSJP)
        - Contribuciones SICOSS (SIJP e INSSJP)
        - Diferencia total
        - Fecha de control
        - Conexión utilizada

2. Control de ART
    - Compara base imponible ART (códigos 100-199, excepto 198)
    - Verifica contribuciones ART (códigos 306, 308)
    - Calcula diferencias por CUIL
    - Incluye control de la fórmula de cálculo ((rem_imp9 * 0.005) + 1172)
    - Almacena resultados en tabla `control_art_diferencias`

3. Control de CUILs
    - Verifica CUILs existentes en DH21 pero no en SICOSS y viceversa
    - Almacena resultados en tabla `control_cuils_diferencias`
    - Campos almacenados:
        - CUIL
        - Origen (DH21 o SICOSS)
        - Fecha de control
        - Conexión utilizada
    - Visualización en interfaz:
        - Badge de color según origen (warning para DH21, danger para SICOSS)
        - Fecha y hora del control
        - CUIL copiable

4. Control de UA/CAD y Carácter
    - Verifica y actualiza dependencia (UA/CAD)
    - Verifica y actualiza carácter (PERM/CONT)

5. Control de Conteos
    - Realiza conteos de registros en tres tablas principales:
        - `dh21aporte`: Registros de aportes y contribuciones en DH21
        - `suc.afip_mapuche_sicoss_calculos`: Registros de cálculos en SICOSS
        - `suc.afip_mapuche_sicoss`: Registros en SICOSS
    - Compara la cantidad de registros entre las tablas
    - Identifica discrepancias en el número de registros
    - Almacena resultados en la sesión para uso posterior
    - No requiere tabla persistente, utiliza la sesión para almacenamiento temporal

6. Control de Conceptos por Período
    - Agrega conceptos específicos de aportes y contribuciones por período fiscal
    - Incluye:
        - Aportes (códigos 201, 202, 203, 204, 205, 247, 248)
        - Contribuciones (códigos 301, 302, 303, 304, 305, 306, 307, 308, 347, 348)
    - Almacena resultados en tabla `control_conceptos_periodos`
    - Campos almacenados:
        - Año y mes del período fiscal
        - Código de concepto
        - Descripción del concepto
        - Importe total
        - Conexión utilizada

## 2. Tablas temporales utilizadas

- dh21aporte: Almacena totales de aportes y contribuciones
- Aportes SIJP DH21
- Aportes INSSJP DH21
- Contribuciones SIJP DH21
- Contribuciones INSSJP DH21
- dh21art: Almacena totales de ART

## 3. Interfaz de Usuario (Filament)

1. Selector de Período Fiscal:
    - Widget dedicado en la parte superior de la interfaz
    - Permite seleccionar año y mes para los controles
    - Opciones:
        - Selección manual mediante selectores desplegables
        - Botones de navegación rápida (mes anterior/siguiente)
        - Botón para restablecer al período actual
    - Comportamiento:
        - Actualiza automáticamente todos los controles al cambiar el período
        - Limpia la caché y resultados anteriores
        - Notifica al usuario sobre el cambio de período
        - Persiste la selección en la sesión del usuario
        - Se muestra como badge en los botones de acción
    - Integración con controles:
        - Todos los controles utilizan el período seleccionado
        - Los resultados se filtran automáticamente por el período activo
        - Las exportaciones y reportes respetan el período seleccionado

2. Pestañas de Control:
    - Resumen: Vista general de diferencias encontradas
    - Diferencias por Aportes: Detalle de diferencias en aportes
    - Diferencias por Contribuciones: Detalle de diferencias en contribuciones
    - CUILs no encontrados: Listado de CUILs que existen en un sistema pero no en otro
    - Conceptos por Período: Detalle de conceptos agrupados por período fiscal

3. Acciones disponibles:
    - Ejecutar todos los controles
    - Ejecutar control específico de aportes
    - Ejecutar control específico de contribuciones
    - Ejecutar control específico de CUILs
    - Ejecutar control de conteos
    - Ejecutar control de conceptos

4. Visualización de resultados:
    - Tablas paginadas (5, 10, 25, 50, 100 registros)
    - Búsqueda y ordenamiento
    - Colores indicativos (warning/danger) según tipo de diferencia
    - Totales calculados automáticamente
    - Detalles expandibles por registro
    - Tarjeta collapsible para control de conteos en la vista de resumen
      - Vista compacta con indicadores de color para cada tipo de conteo
      - Vista expandida con detalles completos y análisis de diferencias
      - Actualización en tiempo real mediante botón dedicado

5. Notificaciones:
    - Éxito/error en la ejecución de controles
    - Resumen de diferencias encontradas
    - Acceso rápido a resultados
    - Notificaciones específicas para cada tipo de control
    - Notificación de cambio de período fiscal

## 4. Consideraciones técnicas

1. Período Fiscal:
    - Se puede especificar año y mes mediante el selector dedicado
    - Por defecto usa el período fiscal actual de la base de datos
    - Implementado mediante el servicio `PeriodoFiscalService`
    - Eventos Livewire para comunicación entre componentes (`fiscalPeriodUpdated`)
    - Almacenamiento en sesión para persistencia entre navegaciones

2. Optimizaciones:
    - Uso de tablas temporales para cálculos
    - Inserción masiva de registros en chunks
    - Caché de resultados del resumen (5 minutos)
    - Queries optimizados con índices
    - Almacenamiento en sesión para datos temporales (conteos)
    - Componentes collapsibles para optimizar espacio en pantalla
    - Invalidación selectiva de caché al cambiar el período fiscal

3. Manejo de errores:
    - Logging detallado de errores
    - Notificaciones amigables al usuario
    - Rollback en caso de fallos

4. Seguridad:
    - Validación de conexiones
    - Control de acceso por roles
    - Sanitización de inputs

5. Experiencia de usuario:
    - Interfaz interactiva con Alpine.js para componentes dinámicos
    - Indicadores visuales de estado (colores, iconos)
    - Diseño responsivo adaptado a diferentes tamaños de pantalla
    - Soporte para modo oscuro en todos los componentes
    - Feedback inmediato al cambiar el período fiscal
