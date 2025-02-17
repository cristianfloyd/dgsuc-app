# Panel de Liquidaciones

Este documento describe el panel de liquidaciones, sus recursos y funcionalidades principales.

## Índice
1. [Configuración del Panel](#configuración-del-panel)
   - [Grupos de Navegación](#grupos-de-navegación)

2. [Recursos](#recursos)
   - [Categorías y Básicos (dh11)](#1-categorías-y-básicos-dh11)
     - [Widgets](#widgets)
     - [Navegación](#navegación)
     - [Funcionalidades](#funcionalidades)
     - [Filtros](#filtros)
     - [Columnas Principales](#columnas-principales)
   - [Liquidaciones (dh21)](#2-liquidaciones-dh21)
     - [Navegación](#navegación-1)
     - [Funcionalidades](#funcionalidades-1)
     - [Columnas Principales](#columnas-principales-1)
   - [Liquidaciones Histórico (dh21h)](#3-liquidaciones-histórico-dh21h)
     - [Navegación](#navegación-2)
     - [Funcionalidades](#funcionalidades-2)
     - [Columnas Principales](#columnas-principales-2)
   - [Básicos Histórico (dh61)](#4-básicos-histórico-dh61)
     - [Navegación](#navegación-3)
     - [Columnas Principales](#columnas-principales-3)

3. [Características Comunes](#características-comunes)
   - [Paginación](#paginación)
   - [Seguridad](#seguridad)
   - [Interfaz](#interfaz)

4. [Notas Técnicas](#notas-técnicas)

## Configuración del Panel

- **ID**: `liquidaciones`
- **Ruta**: `/liquidaciones`
- **Color Principal**: Emerald
- **Ancho Máximo**: Full
- **Barra Lateral**: Completamente colapsable en escritorio

### Grupos de Navegación
1. Personal
2. Liquidaciones

## Recursos

### 1. Categorías y Básicos (dh11)
Gestión de categorías y sus importes básicos.

#### Widgets
##### 1. Selector de Período Fiscal
Widget que permite seleccionar y establecer el período fiscal actual.

**Funcionalidades**:
- Selección de año fiscal (rango de 5 años anteriores hasta 1 año posterior)
- Selección de mes fiscal (1-12)
- Botón para establecer el período seleccionado
- Actualización en tiempo real del período fiscal activo
- Emisión de eventos para sincronización con otros componentes

##### 2. Actualizador de Importes Básicos
Widget para la gestión y actualización masiva de importes básicos.

**Funcionalidades**:
- Selección de escalafón para actualización:
  - Preuniversitario
  - Docente Universitario
  - Autoridad Universitaria
  - Nodocente
  - Todos
- Ingreso de porcentaje de incremento
- Previsualización de cambios antes de aplicar
- Restauración de datos a un período fiscal específico
- Cálculo automático de nuevos importes
- Confirmación de cambios con validación
- Historial de cambios por período fiscal

**Características**:
- Validación de datos antes de aplicar cambios
- Redondeo automático de importes
- Notificaciones de éxito/error
- Sincronización con el período fiscal seleccionado
- Registro de cambios en el historial

#### Navegación
- **Grupo**: Personal
- **Icono**: `heroicon-o-rectangle-stack`
- **Etiqueta**: "Básicos (dh11)"

#### Funcionalidades
- Gestión de importes básicos y asignaciones
- Agrupación por dedicación y escalafón
- Edición en línea de importes básicos

#### Filtros
- Escalafón (Preuniversitario, Docente Universitario, Autoridad Universitaria, Nodocente)
- Estado laboral (A, B, P)

#### Columnas Principales
- Código y descripción de dedicación
- Descripción de categoría
- Escalafón
- Importe básico (editable)
- Importe asignación
- Vigencia (año/mes)
- Controles (cargos, horas, puntos, presupuesto)

### 2. Liquidaciones (dh21)
Gestión de liquidaciones activas.

#### Navegación
- **Grupo**: Liquidaciones
- **Etiqueta**: "Liquidaciones (Dh21)"

#### Funcionalidades
- Visualización de liquidaciones
- Generación de reportes de orden de pago
- Exportación a PDF

#### Columnas Principales
- Número de liquidación
- Legajo
- Cargo
- Concepto
- Importe
- Datos presupuestarios

### 3. Liquidaciones Histórico (dh21h)
Histórico de liquidaciones.

#### Navegación
- **Grupo**: Liquidaciones
- **Etiqueta**: "Liquidaciones Historico"

#### Funcionalidades
- Consulta de liquidaciones históricas
- Paginación optimizada
- Visualizacion de registros históricos

#### Columnas Principales
- Número de liquidación
- Legajo
- Cargo
- Concepto
- Importe
- Datos presupuestarios

### 4. Básicos Histórico (dh61)
Histórico de categorías y básicos.

#### Navegación
- **Grupo**: Personal
- **Etiqueta**: "Basicos Historico"

#### Columnas Principales
- Categoría
- Equivalencia
- Tipo escalafón
- Importe básico
- Dedicación
- Vigencia
- Controles varios

## Características Comunes

### Paginación
- Opción predeterminada: 5 registros por página
- Opciones de paginación: 5, 10, 25, 50, 100, 250, 500, 1000

### Seguridad
- Autenticación requerida
- Middleware de seguridad estándar de Laravel

### Interfaz
- Breadcrumbs habilitados
- Barra lateral colapsable
- Selector de panel en menú de usuario

## Notas Técnicas

- Los recursos utilizan el trait `MapucheConnectionTrait` para la conexión a la base de datos
- Implementación de caché para optimizar consultas frecuentes
- Validaciones numéricas en campos de importes
- Soporte para exportación en múltiples formatos 
