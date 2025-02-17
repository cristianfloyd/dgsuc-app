# Panel de Administración

Este documento describe el panel de administración, sus recursos, widgets y funcionalidades principales.

## Índice
1. [Configuración del Panel](#configuración-del-panel)
2. [Recursos](#recursos)
3. [Widgets](#widgets)
4. [Páginas](#páginas)

## Configuración del Panel

- **ID**: `admin`
- **Ruta**: `/admin`
- **Color Principal**: `#6366f1` (Indigo)
- **Navegación**: Top Navigation
- **Ancho Máximo**: Full
- **Barra Lateral**: Completamente colapsable en escritorio

### Características Base
- Autenticación requerida
- Middleware estándar de Laravel
- Selector de panel en menú de usuario
- Breadcrumbs habilitados

## Recursos

### Gestión de Usuarios (UserResource)

#### Navegación
- **Etiqueta**: "Usuarios"
- **Icono**: `heroicon-o-users`

#### Formulario de Usuario
**Campos**:
- Nombre
- Nombre de usuario
- Contraseña (con confirmación)
- Email
- Fecha de verificación de email
- Foto de perfil

#### Tabla de Usuarios
**Columnas**:
- Nombre
- Email
- Fecha de verificación
- ID de equipo actual
- Foto de perfil
- Fechas de creación/actualización
- Estado de autenticación de dos factores

**Acciones**:
- Editar usuario
- Eliminar usuario (bulk action disponible)

## Widgets

### 1. AdminStatsOverview
Widget de estadísticas generales del sistema.

**Métricas**:
- Total de Usuarios
  - Tendencia de crecimiento
  - Gráfico de evolución
- Uso del Sistema
  - Porcentaje de rendimiento
  - Gráfico de tendencia
- Total de Cargos
  - Distribución y tendencias
  - Gráfico de evolución
- Cargos Activos
  - Porcentaje del total
  - Tendencias temporales
- Categorías
  - Distribución
  - Gráfico de tendencias

### 2. DatabaseHealthWidget
Monitor de salud de la base de datos.

**Métricas**:
- Tamaño de la base de datos
- Información de tablas principales
- Estado de conexión
- Consultas lentas
- Estado de índices

**Características**:
- Actualización cada 5 minutos
- Monitoreo de rendimiento
- Alertas de estado

### 3. SystemStatusWidget
Monitor de estado del sistema.

**Métricas**:
- Conexiones de base de datos
  - Activas vs. Máximas
- Métricas de rendimiento
  - Ratio de cache hits
  - Backends activos
  - Commits/Rollbacks
- Espacio en base de datos
- Métricas de actividad

### 4. MaintenanceLogsWidget
Registro de mantenimiento del sistema.

**Monitoreo**:
- Procesos pendientes
- Estado de backups
- Estado de índices
- Estado de vacuum
- Logs de mantenimiento

### 5. ActivityLogWidget
Registro de actividad del sistema.

**Métricas**:
- Usuarios activos
- Últimas acciones
- Estadísticas de uso
- Monitoreo de tablas

## Monitoreo de Base de Datos Mapuche

### Widgets de Monitoreo
- MapucheStatsWidget
- QueryMonitorWidget
- IndexHealthWidget
- MaintenancePanel

### Alertas y Notificaciones
- Sistema de alertas automáticas
- Notificaciones por email/Slack
- Dashboard de estado

### Mantenimiento
- Programación de tareas
- Registro de actividades
- Control de backups

## Páginas

### 1. Dashboard
- Layout de 3 columnas
- Integración con filtros
- Vista personalizada de widgets

### 2. Perfil de Usuario
**Funcionalidades**:
- Actualización de datos personales
  - Nombre
  - Email
  - Contraseña
  - Foto de perfil
- Validación de contraseña actual
- Notificaciones de actualización
- Gestión de archivos de perfil

## Características Técnicas

### Integración con Base de Datos
- Conexión con PostgreSQL
- Monitoreo de rendimiento
- Gestión de conexiones

### Seguridad
- Autenticación robusta
- Protección CSRF
- Validación de datos
- Gestión de sesiones

### Monitoreo y Mantenimiento
- Métricas en tiempo real
- Logs de actividad
- Estado del sistema
- Salud de la base de datos

### Personalización
- Interfaz responsiva
- Temas personalizados
- Navegación intuitiva
- Notificaciones del sistema

## Notas de Implementación
- Monitoreo constante de recursos
- Caché para optimizar rendimiento
- Sistema de logs detallado

## Implementaciones posibles
- *Mantener un control proactivo de la base de datos*
- *Identificar problemas antes de que sean críticos*
- *Optimizar el rendimiento*
- *Mantener un registro histórico de la salud del sistema*
- *Facilitar la toma de decisiones basada en datos*

## Funcionalidades Adicionales Recomendadas:
1. Monitoreo de Conexiones:
    - Número de conexiones activas
    - Tiempo de conexiones
    - Usuarios conectados
    - Bloqueos de tablas
2. Análisis de Espacio:
    - Tamaño de tablas y sus índices
    - Crecimiento histórico
    - Proyecciones de crecimiento
    - Espacio libre en tablespaces
3. Auditoría de Cambios:
    - Registro de modificaciones importantes
    - Seguimiento de cambios en estructuras
    - Historia de ejecución de scripts
4. Optimización Automática:
    - Sugerencias de índices
    - Detección de consultas problemáticas
    - Recomendaciones de configuración
5. Reportes Programados:
    - Informes diarios/semanales de rendimiento
    - Alertas de umbrales críticos
    - Estadísticas de uso
5. Panel de Backups:
    - Estado de backups
    - Programación de copias
    - Verificación de integridad
    - Restauración de prueba
6. Monitoreo de Replicación (si aplica):
    - Estado de réplicas
    - Lag de replicación
    - Salud de los standby servers
7. Gestión de Permisos:
    - Revisión de roles y permisos
    - Auditoría de accesos
    - Gestión de usuarios de base de datos
