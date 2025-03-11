# Documentación del Reporte Dosuba Sin Liquidar

## Introducción

El reporte "Dosuba Sin Liquidar" es una herramienta diseñada para identificar al personal que no ha sido incluido en las liquidaciones recientes de DOSUBA. Esta documentación explica cómo generar, visualizar y exportar este reporte.

## Acceso al Reporte

1. Ingrese al panel de administración
2. Navegue a la sección **Dosuba** en el menú lateral
3. Seleccione **Generar Reporte**

## Generación del Reporte

### Paso 1: Crear un Nuevo Reporte

1. Haga clic en el botón **Generar Reporte** ubicado en la esquina superior derecha
2. Se abrirá un formulario con los siguientes parámetros:

   ![Formulario de Generación](https://via.placeholder.com/800x400?text=Formulario+de+Generación)

3. **Liquidación Base**: Seleccione la liquidación que servirá como referencia para el reporte. El sistema mostrará las 5 liquidaciones definitivas más recientes.
4. Haga clic en **Crear** para generar el reporte

### Paso 2: Procesamiento

El sistema realizará las siguientes acciones automáticamente:

- Determinará el período fiscal correspondiente a la liquidación seleccionada
- Identificará al personal que debería estar incluido en DOSUBA pero no aparece en las liquidaciones recientes
- Verificará información adicional como estado de embarazo o fallecimiento
- Mostrará los resultados en una tabla interactiva

## Visualización de Resultados

Una vez generado el reporte, se mostrará una tabla con la siguiente información:

| Columna | Descripción |
|---------|-------------|
| Legajo | Número de legajo del empleado |
| Apellido | Apellido del empleado |
| Nombre | Nombre del empleado |
| CUIL | CUIL del empleado (formato XX-XXXXXXXX-X) |
| Unidad Académica | Código de la unidad académica a la que pertenece |
| Última Liquidación | Número de la última liquidación en la que apareció |
| Embarazada | Indicador de si la persona está registrada como embarazada |
| Fallecido | Indicador de si la persona está registrada como fallecida |
| Período | Período fiscal del reporte (formato AAAAMM) |

![Tabla de Resultados](https://via.placeholder.com/800x400?text=Tabla+de+Resultados)

## Funcionalidades de la Tabla

La tabla de resultados ofrece las siguientes funcionalidades:

- **Ordenamiento**: Haga clic en los encabezados de columna para ordenar los datos
- **Búsqueda**: Utilice el campo de búsqueda para filtrar resultados
- **Paginación**: Navegue entre páginas de resultados utilizando los controles de paginación

## Exportación a Excel

Existen dos formas de exportar los datos a Excel:

### Opción 1: Exportar Registros Seleccionados

1. Seleccione los registros que desea exportar marcando las casillas correspondientes
2. Haga clic en el botón **Exportar a Excel** que aparece en la barra de acciones de selección
3. El sistema generará un archivo Excel con los registros seleccionados

### Opción 2: Exportar Reporte Completo

1. Haga clic en el botón **Acciones de Tabla** ubicado en la esquina superior derecha
2. Seleccione **Exportar a Excel**
3. En el formulario emergente, seleccione el período para el cual desea generar el reporte
4. Haga clic en **Exportar**
5. El sistema generará un archivo Excel con el nombre `dosuba-sin-liquidar-AAAAMM.xlsx`

![Opciones de Exportación](https://via.placeholder.com/800x400?text=Opciones+de+Exportación)

## Estructura del Archivo Excel

El archivo Excel generado contiene las siguientes hojas:

1. **Resumen**: Información general del reporte, incluyendo:
   - Total de registros
   - Distribución por unidad académica
   - Distribución por última liquidación
   - Distribución por período fiscal

2. **2 Meses**: Listado detallado de todos los empleados que no han sido liquidados en los últimos dos meses, con la siguiente información:
   - Legajo
   - Apellido
   - Nombre
   - CUIL
   - Unidad Académica
   - Última Liquidación
   - Período

3. **Personal Embarazada**: Listado de personal embarazado que podría requerir atención especial.

4. **Fallecidos**: Listado de personal fallecido según los registros del sistema Mapuche.

5. **Fallecidos Bloqueos**: Listado de personal fallecido registrado a través del sistema de bloqueos pero que aún no está actualizado en el sistema Mapuche.

![Estructura del Excel](https://via.placeholder.com/800x400?text=Estructura+del+Excel)

## Consideraciones Importantes

- El reporte se genera para el período fiscal correspondiente a la liquidación base seleccionada
- Los datos mostrados son temporales y se limpian automáticamente después de un tiempo
- Para obtener información actualizada, se recomienda generar un nuevo reporte
- La información de embarazadas y fallecidos se obtiene de los registros oficiales del sistema

## Solución de Problemas

Si encuentra algún problema al generar o exportar el reporte:

1. Verifique que haya seleccionado una liquidación base válida
2. Asegúrese de tener los permisos necesarios para acceder a los datos
3. Si el reporte no muestra datos, es posible que no haya personal sin liquidar para el período seleccionado
4. En caso de errores persistentes, contacte al administrador del sistema

## Preguntas Frecuentes

**¿Con qué frecuencia debo generar este reporte?**  
Se recomienda generar el reporte mensualmente, después de cada cierre de liquidación.

**¿Puedo modificar los datos del reporte?**  
No, este reporte es de solo lectura. Para corregir información, debe hacerlo en los sistemas de origen.

**¿Cómo se determina si una persona está "sin liquidar"?**  
El sistema compara los registros activos en DOSUBA con las liquidaciones recientes y identifica aquellos que no aparecen en las últimas liquidaciones.

**¿Por qué aparecen personas fallecidas en el reporte?**  
El reporte incluye a todas las personas que deberían estar en DOSUBA según los registros. Las personas fallecidas se marcan específicamente para que se pueda tomar acción sobre estos casos.

---

Para más información o soporte, contacte al equipo de Sistemas Universitarios de Computación. 
