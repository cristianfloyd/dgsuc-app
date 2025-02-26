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

#### Estados de Validación Especiales

Durante la validación, el sistema puede asignar estados especiales según las condiciones encontradas:

1. **Fechas Coincidentes**: Cuando la fecha de baja importada es idéntica a la ya existente en Mapuche.
2. **Fecha Superior**: Cuando la fecha de baja importada es posterior a la fecha registrada en Mapuche.
3. **Licencia Ya Bloqueada**: Cuando se intenta aplicar una licencia a un cargo que ya tiene el stop de liquidación activado.

Estos estados ayudan a identificar situaciones que requieren atención especial antes del procesamiento.

### 3. Procesamiento de Bloqueos

Para procesar los bloqueos:

1. Seleccione un registro y haga clic en "Procesar Bloqueo".
2. O seleccione múltiples registros y utilice la acción masiva "Procesar Seleccionados".
3. Revise la información en la ventana modal.
4. Confirme para aplicar los bloqueos en el sistema Mapuche.

#### Flujo Detallado del Procesamiento

El método `procesarBloqueos` sigue el siguiente flujo de trabajo:

1. **Preparación**:
   - Inicia una transacción en la base de datos
   - Verifica la existencia de la tabla de respaldo (backup)
   - Filtra solo los registros en estado "VALIDADO" y sin mensajes de error

2. **Validación Inicial**:
   - Verifica que existan registros para procesar
   - Si se proporcionan registros específicos, valida que todos estén en estado correcto

3. **Procesamiento por Lotes**:
   - Procesa los registros en grupos de 100 para optimizar el rendimiento
   - Para cada lote:
     - Prepara un backup de los registros actuales en DH03 **antes de modificarlos**
     - Solo se incluyen en el backup los registros que realmente existen en DH03
     - Almacena este backup en la tabla `suc.dh03_backup_bloqueos`

4. **Procesamiento Individual**:
   - Valida la existencia del par legajo-cargo
   - Si el par legajo-cargo no existe, el registro se marca con error y no se procesa
   - Según el tipo de bloqueo:
     - **Licencia**: Activa el flag de stop liquidación
     - **Fallecido/Renuncia**: Actualiza la fecha de baja solo si la nueva fecha es anterior a la existente
   - Actualiza el estado del registro a "PROCESADO" si fue exitoso
   - Elimina el registro de la tabla temporal
   - Registra el resultado en el log del sistema

> **Nota sobre fechas idénticas**: Cuando la fecha de baja importada es idéntica a la ya existente en Mapuche, el sistema:
> - No realiza ninguna actualización en la base de datos
> - No crea un registro de backup (ya que no hay cambios que restaurar)
> - Marca el registro como "PROCESADO" y lo elimina de la tabla temporal
> - Registra en el log que no se realizaron cambios

> **Nota sobre licencias ya bloqueadas**: Cuando se intenta procesar una licencia para un cargo que ya tiene el stop de liquidación activado:
> - No se realiza ninguna actualización en la base de datos
> - Se marca el registro como "PROCESADO" (ya que el estado deseado ya existe)
> - Se registra en el log que no se realizaron cambios

5. **Manejo de Errores**:
   - Si ocurre un error en un registro individual:
     - Marca el registro como "ERROR_PROCESO"
     - Almacena el mensaje de error
     - Continúa con el siguiente registro
   - Si ocurre un error general:
     - Revierte toda la transacción
     - Registra el error en los logs
     - Notifica al usuario

6. **Finalización**:
   - Confirma la transacción si todo fue exitoso
   - Genera un resumen de operaciones:
     - Total de registros procesados
     - Cantidad de operaciones exitosas
     - Cantidad de operaciones fallidas

#### Mecanismo de Backup y Restauración

- El backup se realiza **antes** de modificar cada registro
- Solo se respaldan los registros que realmente existen en DH03
- Cada registro de backup incluye:
  - Información del cargo (nro_cargo, nro_legaj, nro_liqui)
  - Estado original (fec_baja, chkstopliq)
  - Información del bloqueo (tipo_bloqueo, fecha_baja_nueva)
  - Metadatos (fecha_backup, session_id)
- La restauración revierte los cambios utilizando los datos originales almacenados
- El sistema utiliza el ID de sesión para asegurar que solo se restauren los cambios de la sesión actual

#### Consideraciones del Procesamiento

- Solo se procesan registros previamente validados
- El proceso es transaccional: o se completa todo o no se realiza ningún cambio
- Se mantiene un registro detallado de todas las operaciones
- Cada tipo de bloqueo (licencia, fallecido, renuncia) tiene su lógica específica de procesamiento
- El sistema permite la restauración de cambios mediante el backup automático

### 4. Restauración de Cambios

La funcionalidad "Restaurar Cambios" permite revertir las modificaciones realizadas en la tabla DH03 durante el procesamiento de bloqueos.

#### Acceso a la Funcionalidad

- Ubicada en la página de listado de datos importados
- Botón "Restaurar Cambios" con ícono de flecha de retorno
- Color amarillo (warning) para indicar una acción de precaución
- Muestra un indicador numérico con la cantidad de cambios que pueden restaurarse

#### Proceso de Restauración

1. Al hacer clic en el botón "Restaurar Cambios", se muestra un diálogo de confirmación
2. El diálogo advierte que la acción revertirá los últimos cambios realizados en la tabla DH03
3. Al confirmar, el sistema:
   - Recupera todos los registros de backup asociados a la sesión actual del usuario
   - Restaura los valores originales en la tabla DH03 (fechas de baja y estado de bloqueo de liquidación)
   - Elimina los registros de backup una vez restaurados
4. Muestra una notificación de éxito o error según el resultado

#### Características Importantes

- **Segmentación por sesión**: Solo restaura los cambios realizados en la sesión actual del usuario
- **Orden de restauración**: Procesa los registros en orden inverso (del más reciente al más antiguo)
- **Limpieza automática**: Elimina los registros de backup después de restaurarlos
- **Transaccional**: Todo el proceso se ejecuta dentro de una transacción para garantizar la integridad

#### Casos de Uso

Esta funcionalidad es útil cuando:
- Se han procesado bloqueos por error
- Se necesita revertir cambios específicos en la tabla DH03
- Se requiere una forma rápida de deshacer modificaciones recientes

#### Consideraciones de Seguridad

- La restauración está limitada a la sesión actual del usuario
- Requiere confirmación explícita para evitar restauraciones accidentales
- No afecta a los cambios realizados por otros usuarios

### 5. Exportación de Resultados

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
- Los registros con licencias ya bloqueadas se muestran con un fondo amarillo.
- Los registros con fechas superiores a las existentes se muestran con un fondo naranja.

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

### Fechas Coincidentes o Superiores

- Si un registro tiene estado "Fechas Coincidentes", significa que la fecha de baja ya está configurada con el mismo valor en Mapuche.
- Si un registro tiene estado "Fecha Superior", significa que la fecha de baja en el archivo es posterior a la ya registrada en Mapuche.

### Licencias Ya Bloqueadas

Si un registro tiene estado "Licencia Ya Bloqueada", significa que el cargo ya tiene activado el stop de liquidación en Mapuche.

## Mejores Prácticas

1. **Validar antes de procesar**: Siempre valide los registros antes de procesarlos.
2. **Verificar duplicados**: Revise los registros duplicados para evitar bloqueos múltiples.
3. **Exportar resultados**: Exporte los resultados para tener un respaldo de las operaciones realizadas.
4. **Verificar asociación**: Utilice la función "Validar Cargos en Mapuche" para asegurarse de que todos los registros corresponden a cargos existentes.
5. **Revisar estados especiales**: Preste especial atención a los registros con estados como "Fechas Coincidentes", "Fecha Superior" o "Licencia Ya Bloqueada".

---

Para más información o soporte, contacte al equipo de desarrollo. 
