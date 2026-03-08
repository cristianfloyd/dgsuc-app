# Sistema DGSUC - Universidad de Buenos Aires

Sistema integral de gestión de informes de recursos humanos y complementacion de nóminas desarrollado para la Universidad de Buenos Aires, construido con Laravel 12 y FilamentPHP.

## 🚀 Características Principales

- **Múltiples Paneles Administrativos**: Gestión modular a través de diferentes paneles especializados
- **Integración con Sistema HR Mapuche**: Conexión directa con el sistema de recursos humanos institucional
- **Generación SICOSS AFIP**: Procesamiento automático de archivos para declaraciones fiscales
- **Gestión de Embargos**: Sistema completo de procesamiento de descuentos salariales
- **Reportes Avanzados**: Generación de reportes complejos con exportación a Excel
- **Base de Datos Dual**: Arquitectura multi-base de datos con PostgreSQL

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 12 (PHP 8.4+)
- **UI Admin**: FilamentPHP 3.x
- **Autenticación**: Laravel Jetstream, Toba, Cuenta UBA
- **Base de Datos**: PostgreSQL con esquemas múltiples
- **Jobs/Queues**: Sistema de colas de Laravel

### Frontend
- **Tecnologías**: Livewire 3.5+, TailwindCSS, DaisyUI
- **JavaScript**: Alpine.js
- **Build Tool**: Vite

### Bibliotecas Clave
- **Excel**: Laravel Excel, OpenSpout
- **PDFs**: Laravel DomPDF
- **DTOs**: Spatie Laravel Data
- **Autenticación Social**: Laravel Socialite (Microsoft Azure)
- **Números**: KWN Number to Words

## 📋 Requisitos del Sistema

- PHP 8.4+
- PostgreSQL 12+
- Node.js 18+
- Composer 2+
- Redis (para colas)

## ⚡ Instalación Rápida

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/cristianfloyd/informes-app.git
   cd informes-app
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   npm install
   ```

3. **Configurar entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar bases de datos**
   - Editar `.env` con las credenciales de PostgreSQL
   - Configurar conexión principal (`pgsql`) y Mapuche (`pgsql-mapuche`)

5. **Ejecutar migraciones**
   ```bash
   php artisan migrate
   php artisan migrate --database=pgsql-mapuche
   ```

6. **Construir assets**
   ```bash
   npm run build
   ```

7. **Iniciar servidor**
   
   - Solo en modo desarrollo
   ```bash
   php artisan serve
   ```

## 🐳 Docker

La aplicación puede ejecutarse con Docker usando PHP 8.4 (FPM), Nginx y PostgreSQL 17. Se sirve por **https://informes-app.test**. La **base de datos principal** corre en un contenedor; las **bases secundarias** (Mapuche, pgsql-2503, etc.) se conectan al PostgreSQL del **host** (una misma instancia para todas).

### Requisitos

- Docker y Docker Compose
- PostgreSQL en el host (para conexiones secundarias), escuchando en el puerto que indiques (p. ej. 5432 o 5434)
- Para HTTPS local: certificados en `docker/nginx/certs/` (ver más abajo) y entrada en `/etc/hosts` para `informes-app.test`

### Uso rápido

1. **Crear `.env`** (o copiar desde `.env.example`) y opcionalmente definir:

   ```env
   # UID/GID del usuario del host (para permisos con volumen montado)
   UID=1000
   GID=1000
   # O ejecutar: echo "UID=$(id -u)" >> .env && echo "GID=$(id -g)" >> .env

   DB_DATABASE=suc_app
   DB_USERNAME=postgres
   DB_PASSWORD=postgres
   # Conexión a la base del host (secundarias)
   DB_HOST_SECONDARY_PORT=5432
   DB_HOST_SECONDARY_DATABASE=desa
   DB_HOST_SECONDARY_USERNAME=postgres
   DB_HOST_SECONDARY_PASSWORD=postgres
   ```

   El contenedor `app` se ejecuta con el mismo UID/GID del host para evitar problemas con Git, Composer y permisos en `storage`/`vendor`.

2. **Certificados HTTPS y dominio** (para https://informes-app.test):

   - Añade en **/etc/hosts**: `127.0.0.1 informes-app.test`
   - Genera certificados con [mkcert](https://github.com/FiloSottile/mkcert) (recomendado):
     ```bash
     mkcert -install
     cd docker/nginx/certs && mkcert informes-app.test
     ```
   - Detalles y alternativa con OpenSSL en `docker/nginx/certs/README.md`.

3. **Levantar servicios**

   ```bash
   docker compose up -d
   ```

4. **Instalar dependencias y clave** (primera vez)

   ```bash
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   ```

5. **Migraciones** (base principal en el contenedor)

   ```bash
   docker compose exec app php artisan migrate
   ```

6. **Acceder a la app**: **https://informes-app.test**

### Servicios

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| **nginx**| 80, 443 | Nginx (HTTP → HTTPS, Laravel vía PHP-FPM) |
| **app**  | —      | Laravel (PHP 8.4-FPM) |
| **db**   | 5440   | PostgreSQL (solo base principal) |
| **redis**| 6379   | Redis (cache/colas) |

### Bases secundarias (host)

Todas las conexiones secundarias (`pgsql-mapuche`, `pgsql-2503`, `DB_TEST_*`, `DB_TEST_R2_*`, `DB_PROD_*`, etc.) usan por defecto **host.docker.internal** apuntando al PostgreSQL del host. Ajusta en `.env` o en `docker-compose.yml`:

- `DB_HOST_SECONDARY_PORT`: puerto de PostgreSQL en el host (ej. 5432 o 5434).
- `DB_HOST_SECONDARY_DATABASE`, `DB_HOST_SECONDARY_USERNAME`, `DB_HOST_SECONDARY_PASSWORD`: base y credenciales de esa instancia.

En Linux, `host.docker.internal` se resuelve mediante `extra_hosts: host-gateway` en el compose; no hace falta configuración adicional.

### Comandos útiles

```bash
# Colas
docker compose exec app php artisan queue:work

# Artisan
docker compose exec app php artisan sicoss:generar

# Shell
docker compose exec app php artisan tinker
```

## 🏗️ Arquitectura del Sistema

### Paneles FilamentPHP

| Panel | Ruta | Propósito |
|-------|------|-----------|
| **Admin** | `/admin` | Gestión de usuarios y configuración del sistema |
| **AFIP** | `/afip` | Generación de reportes fiscales y SICOSS |
| **Bloqueos** | `/bloqueos` | Administración de restricciones de empleados |
| **Embargos** | `/embargos` | Procesamiento de descuentos judiciales |
| **Liquidaciones** | `/liquidaciones` | Controles Post liquidacione,Basicos |
| **Mapuche** | `/mapuche` | Integración con sistema HR institucional |
| **Reportes** | `/reportes` | Sistema general de reportes |

### Conexiones de Base de Datos

```php
// Conexión principal (aplicación local)
'pgsql' => [
    'search_path' => 'suc_app,informes_app'
]

// Conexión Mapuche (sistema HR)
'pgsql-mapuche' => [
    'search_path' => 'mapuche,suc'
]
```

### Estructura de Modelos

```
app/Models/
├── Mapuche/          # Modelos del sistema HR (conexión pgsql-mapuche)
│   ├── Dh21h.php     # Liquidaciones
│   ├── Dh22.php      # Definición liquidaciones
│   └── ...
├── AfipMapucheSicoss.php  # Datos AFIP/SICOSS (conexión pgsql)
└── ...
```

## 🎯 Comandos Artisan Principales

### Comandos SICOSS
```bash
# Generar archivo SICOSS para todos los empleados
php artisan sicoss:generar

# Generar SICOSS para un empleado específico
php artisan sicoss:generar {legajo}

# Generar datos SICOSS en base de datos
php artisan sicoss:generar-bd {legajo?}

# Probar generación SICOSS
php artisan sicoss:test {legajo}
```

### Comandos de Sincronización
```bash
# Actualizar vista materializada de conceptos
php artisan concepto-listado:refresh

# Sincronizar conceptos desde Mapuche
php artisan concepto-listado:sync

# Actualizar datos de empleados fallecidos
php artisan fallecidos:refresh

# Importar relaciones activas AFIP
php artisan afip:import
```

### Comandos de Desarrollo
```bash
# Servidor de desarrollo
php artisan serve

# Procesar colas
php artisan queue:work

# Optimizar Filament
php artisan filament:optimize

# Ejecutar tests
php artisan test
```

## 🔧 Scripts de Calidad de Código

El proyecto incluye un sistema completo de herramientas de calidad:

```bash
# Ejecutar todas las verificaciones de calidad
composer run quality:check

# Aplicar correcciones automáticas
composer run fix

# Verificaciones específicas
composer run cs-fix        # PHP CS Fixer
composer run lint          # PHP CodeSniffer
composer run rector        # Rector PHP
composer run analyse       # PHPStan
```

## 📊 Funcionalidades Clave

### Panel AFIP - Generación SICOSS

El **Panel AFIP** (`/afip`) es el núcleo del sistema para declaraciones fiscales, organizado en dos grupos principales:

#### 🏛️ **Grupo AFIP**
- **Mapuche SICOSS**: Datos de liquidación procesados desde el sistema HR para declaración AFIP
- **Mi Simplificación**: Interface para exportación de datos a AFIP Mi Simplificación  
- **SICOSS Cálculo**: Módulo de cálculos específicos y ajustes manuales
- **ART (Aseguradoras)**: Gestión de datos para aseguradoras de riesgos de trabajo
- **Relaciones Activas**: Importación y gestión de relaciones laborales vigentes

#### 📊 **Grupo SICOSS**
- **Reporte SICOSS**: Dashboard completo con totales y estadísticas por período
- **Controles SICOSS**: Sistema de validación y detección de diferencias
- **Control Diferencias**: Análisis de discrepancias entre sistemas

#### ⚡ **Proceso de Generación SICOSS**

**Flujo Completo**:
1. **Importación de Datos**: 
   - Relaciones Activas AFIP (archivos TXT)
   - Datos Mapuche SICOSS exportados desde HR
   
2. **Procesamiento Masivo**:
   - 1.2M+ registros de liquidación procesados
   - Actualización de casos especiales no manejados por Mapuche
   - Aplicación de reglas de negocio específicas UBA
   
3. **Controles de Calidad**:
   - **Control CUILs**: Validación de identificadores únicos
   - **Control Aportes**: Verificación de aportes previsionales 
   - **Control Contribuciones**: Validación de contribuciones patronales
   - **Control Conceptos**: Verificación de conceptos por período
   
4. **Generación de Archivos**:
   - Archivos SICOSS en formato AFIP
   - Exportaciones diferenciadas por dependencia
   - Reportes de control y diferencias

#### 🔧 **Funcionalidades Avanzadas**

**Comandos Artisan Especializados**:
```bash
# Generar SICOSS para período específico
php artisan mapuche:generar-sicoss 202412

# Generar con empleados inactivos incluidos  
php artisan mapuche:generar-sicoss 202412 --incluir-inactivos

# Ejecutar controles de validación
php artisan sicoss:ejecutar-controles 202412
```

**Widgets y Dashboards**:
- **SICOSS Totales**: Resumen por dependencia con montos consolidados
- **Relaciones Activas Stats**: Estadísticas de relaciones laborales vigentes  
- **Control Diferencias**: Alertas visuales de discrepancias detectadas

**Optimizaciones de Performance**:
- Procesamiento en chunks para grandes volúmenes
- Jobs en background para operaciones pesadas
- Índices especializados para consultas SICOSS
- Cache de resultados de controles frecuentes

### Panel Embargos - Gestión Judicial

El **Panel Embargos** (`/embargos`) maneja el procesamiento completo de órdenes judiciales y descuentos salariales:

#### ⚖️ **Grupo Liquidaciones**
- **Embargo Resource**: Gestión principal de embargos con configuración de parámetros
- **Configurar Parámetros**: Interface para definir liquidaciones, importes y períodos
- **Dashboard Embargo**: Monitoreo en tiempo real del proceso de descuentos

#### 📊 **Grupo Informes**  
- **Reporte Embargos**: Análisis detallado de embargos por empleado y dependencia
- **Exportación Multi-hoja**: Excel con detalle, resumen y consolidado por unidad académica

#### 🔧 **Flujo de Procesamiento de Embargos**

**1. Configuración de Parámetros**:
- **Período Fiscal**: Selección de período a procesar
- **Liquidación Definitiva**: Número de liquidación base
- **Liquidación Próxima**: Proyección para siguiente período  
- **Liquidaciones Complementarias**: Array de liquidaciones adicionales
- **Inserción DH20**: Flag para escritura en tablas de novedades

**2. Procesamiento Masivo**:
- Validación de embargos activos por legajo
- Cálculo automático según tipo de remuneración
- Aplicación de porcentajes y topes legales
- Generación de registros desde dh21 (conceptos de descuento)

**3. Control de Estados**:
- **Activo**: Embargo en proceso de descuento
- **Suspendido**: Temporalmente inactivo
- **Finalizado**: Completado por monto o plazo
- **Cancelado**: Anulado por resolución judicial

#### 📈 **Tipos de Embargo Soportados**

**Por Tipo de Remuneración**:
- **Haberes**: Sobre sueldo básico y adicionales
- **Complementarios**: Sobre conceptos específicos
- **Retroactivos**: Aplicación a diferencias de períodos anteriores

**Por Modalidad Judicial**:
- **Embargo Preventivo**: Medida cautelar
- **Embargo Ejecutivo**: Por sentencia firme  
- **Retención de Ganancias**: Aplicación fiscal
- **Descuentos Varios**: Otros conceptos judiciales

#### 🎯 **Funcionalidades Avanzadas**

**Cálculos Inteligentes**:
- Aplicación de escalas progresivas según código civil
- Respeto de mínimos inembargables
- Distribución proporcional entre múltiples embargos
- Priorización por orden judicial y fechas

**Reportes Especializados**:
- **Detalle por Empleado**: Historial completo de descuentos
- **Resumen por Juzgado**: Consolidado judicial
- **Control por Unidad Académica**: Impacto organizacional
- **Seguimiento de Pagos**: Estado de transferencias

**Integración con Mapuche**:
- Lectura automática de órdenes judiciales
- Sincronización con datos de nómina
- Validación de legajos activos
- Generación de conceptos DH20 para liquidación

### Panel Bloqueos - Administración de Restricciones

El **Panel Bloqueos** (`/bloqueos`) gestiona las restricciones de empleados por licencias, fallecimientos y renuncias:

#### 🚫 **Grupo Informes**
- **Bloqueos**: Importación y procesamiento de archivos Excel con restricciones de empleados
- **Historial de Bloqueos**: Consulta histórica de todos los bloqueos procesados

#### 📋 **Grupo Consultas**
- **Historial Completo**: Archivo de todas las operaciones de bloqueo realizadas
- **Filtros Avanzados**: Por período fiscal, tipo, estado y fechas de procesamiento

#### 🔄 **Flujo de Procesamiento de Bloqueos**

**1. Importación de Datos**:
- **Archivo Excel**: Carga masiva con validación de formato
- **Campos Requeridos**: Legajo, cargo, fecha de baja, tipo de bloqueo
- **Validación Automática**: Verificación de integridad y duplicados

**2. Estados del Proceso**:
- **Pendiente**: Registro cargado, esperando validación
- **Importado**: Recién cargado en el sistema
- **Duplicado**: Identificado como registro existente
- **Validado**: Aprobado para procesamiento
- **Procesado**: Aplicado exitosamente en Mapuche
- **Error**: Falló en validación o procesamiento

**3. Validaciones Automáticas**:
- **Cargo Asociado**: Verifica existencia del cargo en DH03
- **Fechas Coincidentes**: Valida coherencia temporal
- **Licencia Ya Bloqueada**: Detecta bloqueos duplicados
- **Fecha Cargo**: Verifica coincidencia con período de cargo

#### 📊 **Tipos de Bloqueo Soportados**

**Por Tipo de Restricción**:
- **Licencia** (🔵 Info): Licencias sin goce de haberes
- **Fallecido** (🔴 Peligro): Empleados fallecidos
- **Renuncia** (🟡 Advertencia): Renuncias presentadas

**Estados de Validación**:
- **Fechas Coincidentes** (🟡): Requiere revisión manual
- **Fecha Superior** (🔴): Error en fechas de baja
- **Falta Cargo Asociado** (🔴): Cargo no encontrado en sistema
- **Fecha Cargo No Coincide** (🔴): Inconsistencia temporal

#### ⚙️ **Funcionalidades Avanzadas**

**Procesamiento con Respaldo**:
- **Tabla Backup**: Crea `dh03_backup_bloqueos` automáticamente
- **Rollback Seguro**: Permite reversión de cambios
- **Trazabilidad Completa**: Registro detallado de operaciones
- **Session ID**: Seguimiento por sesión de usuario

**Validación Inteligente**:
```php
// Estados automáticos según validación
- VALIDADO: Listo para procesar
- FALTA_CARGO_ASOCIADO: Error crítico
- FECHA_CARGO_NO_COINCIDE: Error temporal
- LICENCIA_YA_BLOQUEADA: Duplicado detectado
```

**Exportación de Resultados**:
- **Excel Resultados**: Reporte completo del procesamiento
- **Fallecidos Export**: Listado específico para bajas
- **Multi-hoja**: Detalles, resumen y estadísticas

**Integración con Mapuche**:
- **Actualización DH03**: Aplicación de fechas de baja
- **Campo chkstopliq**: Control de parada de liquidación
- **Sincronización Segura**: Transacciones atomicas
- **Backup Automático**: Respaldo antes de cambios

### Sistema de Reportes

El **Panel de Reportes** (`/reportes`) es uno de los módulos más completos del sistema, organizado en grupos funcionales especializados:

#### 📊 **Grupo Informes**
- **Reporte Gerencial**: Consolidado completo de liquidaciones con montos brutos/netos por dependencia y unidad académica
- **Orden de Pago**: Detalle de pagos por banco, función, fuente y programa presupuestario
- **Órdenes de Descuento**: Gestión de descuentos aplicados con clasificación por tipo
- **Comprobantes CHE**: Procesamiento de archivos CHE con validación de formato `cheAAMM.NNNN`
- **Reporte Concepto Listado**: Vista materializada de conceptos con totales agrupados

#### 🏥 **Grupo Dosuba**
- **Dosuba Sin Liquidar**: Empleados con mas de dos meses pendientes de liquidación
- **Reporte Embarazadas**: Seguimiento de personal en situación de embarazo
- **Reporte Fallecidos**: Control de empleados fallecidos para baja en sistemas

#### 🗓️ **Grupo Licencias**
- **Licencias Vigentes**: Estado actual de licencias activas por legajo
- Integración con sistema Mapuche para datos en tiempo real
- Filtros por tipo de licencia, fecha y unidad académica

#### ⚡ **Funcionalidades Avanzadas**

**Exportación Inteligente**:
- Múltiples formatos: Excel estándar, OpenSpout (para archivos grandes), optimizado
- Hojas separadas por categoría (datos, resumen, estadísticas)
- Generación asíncrona para reportes extensos

**Filtros Dinámicos**:
- Por período fiscal y rango de fechas
- Unidad académica y dependencia
- Tipo de empleado y estado laboral
- Criterios monetarios (rangos de montos)

**Performance Optimizada**:
- Uso de sesiones para mantener estado de consultas
- Paginación inteligente para grandes volúmenes
- Índices específicos para consultas frecuentes
- Cache de resultados para reportes repetitivos

**Dashboards Interactivos**:
- Widgets con estadísticas en tiempo real
- Gráficos de tendencias por período
- Indicadores KPI personalizables
- Alertas automáticas para anomalías

### Integración Mapuche
- Sincronización en tiempo real
- Consultas optimizadas a gran escala
- Manejo de caracteres especiales (Malformed Encoding)
- Vistas materializadas para performance

## 🔒 Seguridad y Autenticación

- **Laravel Jetstream**: Autenticación robusta con 2FA
- **Microsoft Azure AD**: Integración SSO institucional
- **Políticas de Acceso**: Control granular por panel y recurso
- **Auditoría**: Registro de cambios y operaciones críticas

## 📈 Optimizaciones de Performance

- **Chunked Queries**: Procesamiento por lotes de registros grandes
- **Background Jobs**: Operaciones pesadas en segundo plano
- **Materialized Views**: Vistas precalculadas para consultas complejas
- **Redis Caching**: Cache distribuido para datos frecuentes
- **Database Indexing**: Índices optimizados para consultas críticas

## 🧪 Testing

```bash
# Ejecutar suite de tests completa
php artisan test

# Tests específicos
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 📚 Documentación Adicional

- [Documentación Técnica](docs/)
- [Guía de Desarrollo](docs/filament/)
- [Optimizaciones SICOSS](DOCUMENTACION_OPTIMIZACIONES_SICOSS.md)
- [Comandos Personalizados](docs/commands/)

## 🤝 Contribución

1. Fork del proyecto
2. Crear rama de feature (`git checkout -b feature/AmazingFeature`)
3. Commit de cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Crear Pull Request

### Estándares de Código

- Seguir PSR-12 para PHP
- Usar PHP CS Fixer y PHPStan
- Documentar métodos públicos
- Escribir tests para nuevas funcionalidades
- Ejecutar `composer run quality:check` antes de commit

## 📝 Licencia

Este proyecto es propiedad de la Universidad de Buenos Aires y está bajo licencia MIT.

## 🆘 Soporte

Para reportar issues o solicitar features:

1. Crear issue en el repositorio
2. Incluir pasos para reproducir el problema
3. Especificar versión de PHP y Laravel
4. Adjuntar logs relevantes si aplica

## 👥 Créditos

Desarrollado para la **Universidad de Buenos Aires** como parte del sistema integral de gestión de recursos humanos y liquidaciones.

---

**Versión**: Laravel 12.x | **PHP**: 8.4+ | **Estado**: En Producción ✅