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

5. Control de Conceptos por Período
    - Verifica los importes de conceptos específicos en un período fiscal
    - Incluye conceptos de aportes (201-205, 247, 248) y contribuciones (301-308, 347, 348)
    - Almacena resultados en tabla `control_conceptos_periodos`
    - Campos almacenados:
        - Código de concepto
        - Descripción
        - Importe
        - Año y mes
        - Fecha de control
        - Conexión utilizada

## 2. Tablas temporales utilizadas

- dh21aporte: Almacena totales de aportes y contribuciones
- Aportes SIJP DH21
- Aportes INSSJP DH21
- Contribuciones SIJP DH21
- Contribuciones INSSJP DH21
- dh21art: Almacena totales de ART

## 3. Interfaz de Usuario (Filament)

1. Pestañas de Control:
    - Resumen: Vista general de diferencias encontradas
    - Diferencias por Aportes: Detalle de diferencias en aportes
    - Diferencias por Contribuciones: Detalle de diferencias en contribuciones
    - CUILs no encontrados: Listado de CUILs que existen en un sistema pero no en otro
    - Conceptos por Período: Detalle de importes por concepto en el período fiscal

2. Acciones disponibles:
    - Ejecutar todos los controles
    - Ejecutar control específico de aportes
    - Ejecutar control específico de contribuciones
    - Ejecutar control específico de CUILs
    - Ejecutar control específico de conceptos
    - Ejecutar control de conteos
    - Exportar resultados a Excel

3. Visualización de resultados:
    - Tablas paginadas (5, 10, 25, 50, 100 registros)
    - Búsqueda y ordenamiento
    - Colores indicativos (warning/danger) según tipo de diferencia
    - Totales calculados automáticamente
    - Detalles expandibles por registro

4. Notificaciones:
    - Éxito/error en la ejecución de controles
    - Resumen de diferencias encontradas
    - Acceso rápido a resultados

## 4. Exportación de Datos

1. Funcionalidad de Exportación:
    - Botón de exportación en cada pestaña activa
    - Exportación a formato Excel (.xlsx)
    - Nombre de archivo incluye tipo de reporte, período fiscal y timestamp
    - Deshabilitado en la pestaña de resumen

2. Tipos de Exportación:
    - Diferencias por Aportes
    - Diferencias por Contribuciones
    - CUILs no encontrados
    - Conceptos por Período

3. Formato de Exportación:
    - Encabezado con título del reporte
    - Período fiscal (año-mes)
    - Fecha y hora de generación
    - Estilos mejorados para encabezados
    - Filas alternas con colores para mejor legibilidad
    - Bordes en todas las celdas
    - Autoajuste del ancho de columnas
    - Pestaña con nombre específico según el reporte

4. Implementación Técnica:
    - Uso de Laravel Excel (maatwebsite/excel)
    - Clases de exportación específicas para cada tipo de reporte
    - Clase base abstracta para compartir funcionalidad común
    - Implementación de interfaces para estilos y formato
    - Manejo de errores y notificaciones

## 5. Consideraciones técnicas

1. Período Fiscal:
    - Se puede especificar año y mes
    - Por defecto usa el período fiscal actual de la base de datos

2. Optimizaciones:
    - Uso de tablas temporales para cálculos
    - Inserción masiva de registros en chunks
    - Caché de resultados del resumen (5 minutos)
    - Queries optimizados con índices

3. Manejo de errores:
    - Logging detallado de errores
    - Notificaciones amigables al usuario
    - Rollback en caso de fallos
    - Validación de tipos de archivo en exportaciones

4. Seguridad:
    - Validación de conexiones
    - Control de acceso por roles
    - Sanitización de inputs
    - Validación de datos exportados
