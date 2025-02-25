# Documentación del Recurso de Bloqueos

## Introducción

El módulo de Bloqueos permite gestionar el proceso de bloqueo de cargos en el sistema Mapuche. Este recurso facilita la importación, validación y procesamiento de solicitudes de bloqueo para diferentes tipos de movimientos como licencias, renuncias o fallecimientos.

## Acceso al Módulo

El módulo de Bloqueos se encuentra en el panel de Reportes, identificado con el ícono de "rectangle-stack" y la etiqueta "Bloqueos".

## Funcionalidades Principales

### 1. Importación de Datos

Para importar nuevos bloqueos:

1. Haga clic en el botón "Importar datos" en la parte superior derecha de la pantalla.
2. Seleccione la liquidación correspondiente del desplegable.
3. Cargue el archivo Excel con el formato establecido (debe contener columnas como legajo, cargo, tipo de movimiento, etc.).
4. Haga clic en "Importar" para procesar el archivo.

> **Nota**: El sistema validará automáticamente el formato del archivo y detectará registros duplicados.

### 2. Validación de Registros

Existen varias opciones para validar los registros importados:

#### Validación Individual

1. Para cada registro, utilice el botón "Validar" en la columna de acciones.
2. El sistema verificará si el par legajo-cargo existe en Mapuche.
3. El estado del registro se actualizará automáticamente.

#### Validación Masiva

1. Seleccione los registros que desea validar utilizando las casillas de verificación.
2. Utilice la acción masiva "Validar Registros".
3. Confirme la acción en el diálogo de confirmación.
4. El sistema mostrará un resumen de los resultados.

#### Validación de Todos los Registros

1. Utilice el botón "Validar Todos" en la parte superior derecha.
2. Confirme la acción.
3. El sistema procesará todos los registros y mostrará un resumen.

#### Validación de Cargos en Mapuche

1. Utilice el botón "Validar Cargos en Mapuche" en la parte superior.
2. El sistema verificará si cada par legajo-cargo existe en Mapuche.
3. Se actualizará el indicador "Cargo en Mapuche" para cada registro.

### 3. Procesamiento de Bloqueos

Para procesar los bloqueos:

1. Seleccione un registro y haga clic en "Procesar Bloqueo".
2. O seleccione múltiples registros y utilice la acción masiva "Procesar Seleccionados".
3. Revise la información en la ventana modal.
4. Confirme para aplicar los bloqueos en el sistema Mapuche.

### 4. Exportación de Resultados

Para exportar los resultados:

1. Seleccione los registros que desea exportar.
2. Utilice la acción masiva "Exportar a Excel".
3. El sistema generará un archivo Excel con los resultados.

## Interfaz de Usuario

### Tabla Principal

La tabla principal muestra los siguientes campos:

- **Estado**: Indica el estado actual del registro (Pendiente, Validado, Error, etc.).
- **Nro. Legajo**: Número de legajo del empleado.
- **Nro. Cargo**: Número de cargo asociado.
- **Fecha Baja**: Fecha de baja solicitada.
- **Fecha dh03**: Fecha de baja registrada en Mapuche.
- **Tipo**: Tipo de movimiento (licencia, renuncia, fallecimiento).
- **Cargo en Mapuche**: Indica si el cargo existe en Mapuche.

> **Nota**: Puede personalizar las columnas visibles utilizando el selector de columnas.

### Filtros Disponibles

- **Tipo**: Filtra por tipo de movimiento.
- **Estado**: Filtra por estado de procesamiento.

### Indicadores Visuales

- Los registros con fechas coincidentes se muestran con un fondo verde.
- Los registros de tipo "licencia" se muestran con un fondo azul.
- Los registros con errores se destacan en rojo.

## Edición de Registros

Para editar un registro:

1. Haga clic en el botón "Editar" en la columna de acciones.
2. Modifique los campos necesarios.
3. Haga clic en "Guardar" para aplicar los cambios.

## Verificación en Mapuche

Desde la pantalla de edición, puede:

1. Utilizar el botón "Verificar en Mapuche" para comprobar si el legajo y cargo existen.
2. Ver el historial de cambios utilizando el botón "Ver Historial".

## Resolución de Problemas Comunes

### Registros Duplicados

Los registros duplicados se marcan automáticamente durante la importación. Puede identificarlos por su estado "Duplicado".

### Errores de Validación

Si un registro no puede ser validado, se mostrará un mensaje de error específico en la columna "Mensaje Error".

### Problemas de Procesamiento

Si ocurre un error durante el procesamiento, verifique:
- Que el par legajo-cargo exista en Mapuche.
- Que las fechas sean coherentes.
- Que el tipo de movimiento sea válido.

## Mejores Prácticas

1. **Validar antes de procesar**: Siempre valide los registros antes de procesarlos.
2. **Verificar duplicados**: Revise los registros duplicados para evitar bloqueos múltiples.
3. **Exportar resultados**: Exporte los resultados para tener un respaldo de las operaciones realizadas.
4. **Verificar asociación**: Utilice la función "Validar Cargos en Mapuche" para asegurarse de que todos los registros corresponden a cargos existentes.

---

Para más información o soporte, contacte al equipo de desarrollo. 