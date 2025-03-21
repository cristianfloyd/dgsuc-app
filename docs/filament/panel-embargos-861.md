# Manual de Usuario: Sistema de Gestión de Embargos

## Contenido

  1. Introducción
  2. Acceso al Módulo
  3. Listado de Embargos
  4. Configuración de Parámetros
  5. Proceso de Ejecución
  6. Preguntas Frecuentes

## Introducción

El módulo de Embargos es una herramienta diseñada para gestionar el procesamiento de embargos sobre liquidaciones salariales. Permite configurar diferentes parámetros para la ejecución del proceso, visualizar los resultados y mantener un registro organizado de las operaciones realizadas.

Funcionalidades principales:

- Configuración de parámetros de embargos
- Procesamiento de cálculos según liquidaciones seleccionadas
- Visualización de resultados en formato tabular
- Posibilidad de exportar datos para reportes
- Selección de periodos fiscales para análisis históricos

## Acceso al Módulo

Para acceder al módulo de Embargos:

1. Ingrese al sistema con sus credenciales
2. En el menú lateral, busque la sección "Liquidaciones"
3. Haga clic en "Embargo"

## Listado de Embargos

La página principal muestra un listado de los embargos procesados con la siguiente información:

| Campo | Descripción |
|-------|-------------|
| Nro. Liquidación | Identificador de la liquidación asociada |
| Tipo Embargo | Clasificación del tipo de embargo (códigos 265, 267, etc.) |
| Nro. Legajo | Número de legajo del empleado afectado |
| Remunerativo | Monto remunerativo considerado |
| No Remunerativo | Monto no remunerativo considerado |
| Total | Importe total del embargo |
| Código Concepto | Código que identifica el concepto en el sistema (siempre 861) |

## Widgets Superiores

En la parte superior de la pantalla encontrará:
Selector de Periodo Fiscal: Permite seleccionar el año y mes para filtrar la información
Panel de Propiedades: Muestra los parámetros configurados actualmente
Acciones Disponibles
Configurar Parámetros: Abre la pantalla de configuración del proceso
Reset: Restablece los valores a sus configuraciones predeterminadas
Actualizar Datos: Ejecuta el proceso de embargo con los parámetros configurados

## Configuración de Parámetros

La pantalla de configuración permite establecer los parámetros necesarios para ejecutar el proceso de embargos.

### Campos de Configuración

- Liquidación Definitiva: Seleccione la liquidación base para el cálculo de embargos. Este es el proceso principal cuyas novedades se utilizarán.
- Liquidación Próxima: Indique la liquidación donde se aplicarán los embargos calculados. Por defecto se copia el valor de la liquidación definitiva.
- Liquidaciones Complementarias: Seleccione una o varias liquidaciones complementarias que deban considerarse en el cálculo (opcional). Estas liquidaciones aportan información adicional al cálculo principal.
- Insertar en DH25: Active esta opción si desea que los registros calculados se inserten automáticamente en la tabla DH25 del sistema Mapuche. Si no se activa, sólo se calcularán y mostrarán los resultados sin afectar la base de datos operativa.

## Periodo Fiscal

El periodo fiscal se selecciona desde el widget superior. Este valor es crucial ya que:
Filtra las liquidaciones disponibles para selección
Establece el periodo de vigencia de los embargos generados
Se utiliza para los registros generados en el sistema

## Proceso de Ejecución

Para ejecutar el proceso de embargos:

- Configure los parámetros en la pantalla de configuración
- Verifique el periodo fiscal seleccionado
- Haga clic en Guardar para confirmar la configuración
- En la pantalla principal, haga clic en Actualizar Datos
- El sistema procesará la información y mostrará los resultados en la tabla
- Se visualizará un mensaje indicando la cantidad de registros procesados

## Detalle del Proceso

El sistema ejecuta internamente la función emb_proceso que:

1. Obtiene los legajos con embargos de la liquidación definitiva
2. Procesa la información de las liquidaciones complementarias
3. Calcula los montos según el tipo de embargo
4. Genera los registros correspondientes
5. Opcionalmente inserta en la tabla DH25 si se activó esta opción

## Preguntas Frecuentes

### ¿Por qué no veo liquidaciones en los selectores?

Verifique que el periodo fiscal seleccionado contenga liquidaciones. Si el problema persiste, consulte con el administrador para verificar la conexión a la base de datos.

### ¿Qué significa cada tipo de embargo?

- 265/274: Embargos sobre el bruto (se calcula sobre el total bruto)
- 267: Embargos sobre remunerativos
- 268: Embargos sobre el neto (se calcula después de descuentos)

### ¿Puedo procesar embargos de periodos anteriores?

Sí, seleccionando el periodo fiscal correspondiente en el selector. Tenga en cuenta que el sistema utilizará las liquidaciones disponibles para ese periodo.

### ¿Cómo sé si los datos fueron insertados en DH25?

Si activó la opción "Insertar en DH25" y el proceso se ejecutó correctamente, verá un mensaje de éxito indicando la cantidad de registros procesados. Puede verificar en el sistema Mapuche los registros insertados.

### ¿Puedo exportar los resultados?

Actualmente puede usar la funcionalidad de exportación estándar de las tablas. En futuras versiones se implementarán opciones específicas de exportación para este módulo.

#### Nota importante

Este módulo interactúa con la base de datos Mapuche. Asegúrese de tener los permisos adecuados antes de ejecutar procesos que modifiquen datos (como la inserción en DH25).
Para cualquier consulta adicional, contacte al equipo de soporte técnico.

Documento generado: Marzo 2025
Versión del módulo: 1.0
