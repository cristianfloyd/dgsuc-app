# Panel de Embargos

Este documento describe el panel de embargos, sus recursos y funcionalidades principales.

## Índice
1. [Configuración del Panel](#configuración-del-panel)
2. [Recursos](#recursos)
   - [Embargo Resource](#embargo-resource)
   - [Páginas](#páginas)
   - [Widgets](#widgets)
3. [Características Técnicas](#características-técnicas)

## Configuración del Panel

- **ID**: `embargos`
- **Ruta**: `/embargos`
- **Color Principal**: Amber
- **Notificaciones**: Habilitadas (Database Notifications)

### Características Base
- Autenticación requerida
- Middleware estándar de Laravel
- Selector de panel en menú de usuario
- Account Widget integrado

## Recursos

### Embargo Resource

#### Navegación
- **Grupo**: Liquidaciones
- **Etiqueta**: "Embargo"
- **Slug**: `embargos`
- **Icono**: `heroicon-o-rectangle-stack`

#### Propiedades Principales
- Período Fiscal
- Número de Liquidación Próxima
- Números de Liquidaciones Complementarias
- Número de Liquidación Definitiva
- Flag de Inserción en DH25

#### Tabla Principal
**Columnas**:
- Número de Liquidación
- Tipo de Embargo
- Número de Legajo
- Importe Remunerativo (ARS)
- Importe No Remunerativo (ARS)
- Total (ARS)
- Código de Concepto

**Acciones**:
- Actualizar Datos
- Configurar Parámetros

### Páginas

#### 1. Lista de Embargos (ListEmbargos)
- Vista principal del recurso
- Widgets en encabezado:
  - Selector de Período Fiscal
  - Display Properties Widget
- Acciones de encabezado:
  - Configurar Parámetros
  - Reset (restaura valores por defecto)
- Ancho máximo: ScreenExtraLarge
- Layout de 2 columnas para widgets

#### 2. Dashboard de Embargo (DashboardEmbargo)
- Panel de control específico para embargos
- Gestión de parámetros:
  - Liquidaciones complementarias
  - Liquidación definitiva
  - Liquidación próxima
  - Control de inserción en DH25
- Funcionalidad de actualización de datos

#### 3. Configuración de Parámetros (ConfigureEmbargoParameters)
**Formulario de Configuración**:
- Selector de Liquidación Definitiva
  - Opciones filtradas por período fiscal
- Input para Número de Liquidación Próxima
- Selector múltiple de Liquidaciones Complementarias
  - Opciones filtradas por período fiscal
- Toggle para Inserción en DH25
- Widget de Período Fiscal en encabezado

#### 4. Edición de Embargo (EditEmbargo)
- Funcionalidad estándar de edición

### Widgets

#### 1. Display Properties Widget
- Vista personalizada para propiedades
- Actualización en tiempo real
- Escucha eventos de actualización de propiedades
- Montaje inicial con propiedades predefinidas

## Características Técnicas

### Traits y Patrones
- Uso del trait `DisplayResourceProperties`
- Implementación de caché para propiedades
- Sistema de eventos para sincronización de datos

### Integración con Base de Datos
- Conexión con tablas de Mapuche
- Gestión de liquidaciones (DH22)
- Procesamiento de resultados de embargos

### Eventos y Comunicación
- Evento `propertiesUpdated` para sincronización
- Evento `updated-periodo-fiscal` para actualización de período
- Sistema de notificaciones para feedback al usuario

### Seguridad
- Middleware de autenticación
- Protección CSRF
- Sesiones seguras

### Personalización
- Formularios responsivos
- Validación de datos
- Gestión de estados
- Notificaciones de sistema
- Interfaz adaptativa

## Notas de Implementación
- Los cambios en parámetros requieren confirmación
- La actualización de datos es asíncrona
- Se mantiene historial de cambios
- Implementación de caché para optimizar rendimiento
- Sistema de rollback para cambios críticos 
