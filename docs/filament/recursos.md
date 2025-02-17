# Documentación de Recursos Filament

Este documento describe los recursos disponibles en el panel de administración Filament y sus principales funcionalidades.

## Índice
1. [Dashboard](#dashboard)
2. [Bloqueos](#bloqueos)
3. [Comprobante de Nómina](#comprobante-de-nómina)
4. [Dosuba Sin Liquidar](#dosuba-sin-liquidar)
5. [Embargos](#embargos)
6. [Orden de Pago](#orden-de-pago)
7. [Órdenes de Descuento](#órdenes-de-descuento)
8. [Reporte de Embarazadas](#reporte-de-embarazadas)
9. [Reporte de Fallecidos](#reporte-de-fallecidos)
10. [Reporte Gerencial](#reporte-gerencial)
11. [Listado de Conceptos](#listado-de-conceptos)

## Dashboard
Panel de control principal que muestra una vista general del sistema.
- **Ruta**: `/reportes/dashboard`
- **Icono**: `heroicon-o-home`
- **Título**: "Panel de Reportes"

## Bloqueos
Gestión de bloqueos de liquidación.

### Funcionalidades
- Importación de datos desde Excel
- Procesamiento de bloqueos
- Validación de registros
- Exportación a Excel
- Gestión de cargos relacionados

### Filtros
- Tipo de bloqueo (Licencia, Fallecido, Renuncia)
- Estado de procesamiento

### Acciones
- Editar registro
- Procesar bloqueo
- Validar registro
- Exportar resultados
- Procesamiento masivo

## Comprobante de Nómina
Gestión de comprobantes de nómina (CHE).

### Funcionalidades
- Importación de archivos CHE
- Exportación en formato PDF y Excel
- Importación rápida y avanzada
- Generación de comprobantes

### Filtros
- Unidad Académica
- Dependencia
- Liquidación

## Dosuba Sin Liquidar
Gestión de registros Dosuba pendientes de liquidación.

### Funcionalidades
- Visualización de registros sin liquidar
- Exportación a Excel
- Filtrado por período y liquidación

### Columnas principales
- Legajo
- CUIL
- Unidad Académica
- Estado de embarazo/fallecimiento
- Período fiscal

## Embargos
Gestión de embargos judiciales.

### Funcionalidades
- Seguimiento de embargos
- Cálculo de importes descontados
- Visualización de saldos pendientes

### Filtros
- Estado del embargo
- Tipo de embargo
- Juzgado

### Columnas principales
- Número de embargo
- Legajo
- Importe total
- Saldo pendiente
- Estado

## Orden de Pago
Gestión de órden de pago.

### Funcionalidades
- Generación del reporte Orden de Pago
- Vista previa de la tabla
- Visualizacion y exportacion en formato pdf y excel. 
- Selección múltiple de liquidaciones

### Columnas principales
- Número de liquidación
- Banco
- Función
- Importes (bruto, descuentos, total)

## Órdenes de Descuento
Gestión de órdenes de descuento y aportes.

### Funcionalidades
- Sincronización con base de datos Mapuche
- Exportación múltiple (descuentos y aportes)
- Filtrado avanzado por periodo, unidad acad, periodo de liquidacion, liquidacion.

### Acciones
- Poblar tabla
- Exportar todo
- Exportar órdenes de descuento
- Exportar aportes y contribuciones
- Limpiar tabla

## Reporte de Embarazadas
Gestión de personal en estado de embarazo.

### Funcionalidades
- Actualización automática de datos
- Exportación a Excel
- Gestión de registros

### Columnas principales
- Legajo
- Apellido y nombre
- Unidad académica

## Reporte de Fallecidos
Gestión de registros de personal fallecido.

### Funcionalidades
- Actualización desde fecha específica
- Exportación a Excel
- Gestión de registros históricos

### Columnas principales
- Legajo
- Apellido y nombre
- CUIL
- Fecha de defunción

## Reporte Gerencial
Reporte gerencial consolidado. (beta)

### Funcionalidades
- Visualización de datos consolidados
- Filtrado avanzado
- Exportación de datos

### Filtros
- Dependencia
- Unidad Académica
- Escalafón
- Liquidación
- Cobro por banco

### Columnas principales
- Legajo
- Apellido y nombre
- Importes (bruto, neto, descuentos)
- Liquidación

## Listado de Conceptos
Gestión de conceptos de liquidados.

### Funcionalidades
- Filtrado por período y liquidación
- Gestión de caché
- Exportación de datos en excel.

### Filtros
- Período fiscal
- Liquidación
- Concepto

### Columnas principales
- Liquidación
- Legajo
- Dependencia
- Concepto
- Importe 
