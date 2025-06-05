# Refactorización CargoSacService - Plan de Modernización

## 📋 **Resumen del Proyecto**

La clase `CargoSacService` contiene código legacy que necesita ser refactorizado para integrarse en una aplicación Laravel moderna siguiendo las mejores prácticas del framework y los patrones de diseño establecidos.

**Fecha de inicio:** Diciembre 2024  
**Estado:** En desarrollo  
**Prioridad:** Alta  

## 🔍 **Análisis del Problema**

### **Problemas Identificados en el Código Legacy:**

#### ❌ **Arquitectura y Patrones:**

- [ ] Métodos estáticos que dificultan testing e inyección de dependencias
- [ ] SQL crudo en lugar de Eloquent ORM
- [ ] Violación de principios SOLID (responsabilidad única)
- [ ] Falta del patrón Repository
- [ ] No hay separación entre lógica de negocio y acceso a datos

#### ❌ **Código y Estándares:**

- [ ] Nombres de variables y métodos en español
- [ ] Arrays PHP antiguos en lugar de Collections
- [ ] Falta de tipos de parámetros y retorno
- [ ] Métodos extremadamente largos (>200 líneas)
- [ ] Falta de documentación PHPDoc consistente

#### ❌ **Funcionalidad:**

- [ ] Lógica compleja sin separación de responsabilidades
- [ ] Manejo de fechas con funciones nativas en lugar de Carbon
- [ ] No usa Laravel Data para DTOs
- [ ] Falta de validación estructurada

## 🚀 **Plan de Refactorización**

### **Fase 1: Modelos Eloquent** ✅ *En Progreso*

#### **✅ Modelos Existentes Verificados:**

- [x] `Dh01` - Datos personales de empleados
- [x] `Dh03` - Cargos de empleados  
- [x] `Dh10` - Brutos acumulados SAC
- [x] `Dh05` - Licencias
- [x] `Dh09` - Datos adicionales empleado
- [x] `MapucheConfig` - Configuración del sistema
- [x] `PeriodoFiscalService` - Servicio de períodos fiscales

#### **✅ Modelos Implementados:**

- [x] `Dl02` - Variantes de Licencias (Completado)

#### **❌ Modelos Faltantes:**

- [ ] `Dh12` - Conceptos de liquidación
- [ ] `Dh21` - Liquidaciones de conceptos  
- [ ] `Dh99` - Período fiscal actual
- [ ] Mejoras en `Dh10` - Agregar relaciones faltantes

### **Fase 2: Data Transfer Objects (DTOs)** ❌ *Pendiente*

```php
- [ ] SacCargoData
- [ ] SacLegajoData  
- [ ] SacCargoVigenteData
- [ ] SacFiltrosData
- [ ] SacPeriodoData
```

### **Fase 3: Repository Pattern** ❌ *Pendiente*

```php
- [ ] SacRepository
- [ ] SacRepositoryInterface
- [ ] VinculoCargoRepository
```

### **Fase 4: Services Modernos** ❌ *Pendiente*

```php
- [ ] ModernCargoSacService
- [ ] VinculoCargoService  
- [ ] SacCalculationService
- [ ] SacValidationService
```

### **Fase 5: Resources Filament** ❌ *Pendiente*

```php
- [ ] SacCargoResource
- [ ] SacReportResource
- [ ] SacCargoWidget
```

### **Fase 6: Testing** ❌ *Pendiente*

```php
- [ ] Unit Tests para Services
- [ ] Unit Tests para Repositories
- [ ] Feature Tests para Resources
- [ ] Integration Tests
```

## 📝 **Detalles de Implementación**

### **✅ Modelo Dl02 - Variantes de Licencias**

**Ubicación:** `app/Models/Mapuche/Catalogo/Dl02.php`

**Características implementadas:**

- [x] 41 campos basados en DDL real
- [x] Tipos de datos precisos (boolean, integer, decimal, string)
- [x] Comentarios PHPDoc completos
- [x] 10+ Scopes de consulta especializados
- [x] Métodos de negocio para validaciones
- [x] Relación con Dh05 (licencias)
- [x] Métodos para cálculos de remuneración

**Scopes implementados:**

- [x] `remuneradas()` - Licencias remuneradas
- [x] `noRemuneradas()` - Licencias no remuneradas  
- [x] `maternidad()` - Licencias por maternidad
- [x] `absorcion()` - Licencias por absorción
- [x] `generaVacante()` - Licencias que generan vacante
- [x] `computaAntiguedad()` - Que computan antigüedad
- [x] `porEscalafon()` - Filtrar por escalafón
- [x] `porSexo()` - Filtrar por sexo
- [x] `conControlFechas()` - Con control de fechas
- [x] `ordenadoPorAplicacion()` - Ordenar por aplicación

### **❌ Modelo Dh12 - Conceptos** *Pendiente*

**Ubicación:** `app/Models/Mapuche/Dh12.php`

**Campos requeridos:**

```php
- codn_conce (integer, PK)
- desc_conce (string)
- tipo_conce (string) // H/D
- activo (boolean)
- formula (string, nullable)
```

**Relaciones requeridas:**

- HasMany con Dh21 (liquidaciones)
- HasMany con Dh19 (beneficiarios)

### **❌ Modelo Dh21 - Liquidaciones** *Pendiente*

**Ubicación:** `app/Models/Mapuche/Dh21.php`

**Campos requeridos:**

```php
- nro_liqui (integer, PK compuesta)
- nro_legaj (integer, PK compuesta)  
- nro_cargo (integer, PK compuesta)
- codn_conce (integer, PK compuesta)
- impp_conce (decimal)
- nov1_conce (decimal)
- nov2_conce (decimal)
- tipo_conce (string)
```

### **❌ Modelo Dh99 - Período Fiscal** *Pendiente*

**Ubicación:** `app/Models/Mapuche/Dh99.php`

**Campos requeridos:**

```php
- per_anoct (integer)
- per_mesct (integer)
- desc_periodo (string, nullable)
```

## 🎯 **DTOs Planificados**

### **SacCargoData**

```php
<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;

class SacCargoData extends Data
{
    public function __construct(
        public int $nro_cargo,
        public int $vcl_cargo,
        public array $importes_brutos,
        public array $importes_acumulados,
        public array $vinculos,
        public int $primer_semestre,
        public int $segundo_semestre,
        // ... más campos
    ) {}
}
```

### **SacLegajoData**

```php
<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class SacLegajoData extends Data
{
    public function __construct(
        public int $nro_legajo,
        public string $apellido_nombres,
        public string $documento,
        public string $tipo_documento,
        public string $dependencia,
        /** @var DataCollection<SacCargoData> */
        public DataCollection $cargos,
        /** @var DataCollection<SacCargoVigenteData> */
        public DataCollection $cargos_vigentes,
    ) {}
}
```

## 🔧 **Servicios Planificados**

### **ModernCargoSacService**

```php
<?php

namespace App\Services\Mapuche;

class ModernCargoSacService
{
    public function __construct(
        private SacRepository $sacRepository,
        private PeriodoFiscalService $periodoService,
        private VinculoCargoService $vinculoService
    ) {}

    public function obtenerBrutosSacCargo(int $legajo, int $nroCargo): ?SacCargoData
    public function procesarBrutosParaSac(array $filtros, string $orderBy = ''): Collection
    public function actualizarCargo(int $nroCargo, array $datos): bool
    public function obtenerInfoMes(int $mes, int $anio): array
}
```

### **SacRepository**

```php
<?php

namespace App\Repositories\Mapuche;

class SacRepository
{
    public function getBrutosSacCargo(int $legajo, int $nroCargo): ?SacCargoData
    public function getBrutosParaSac(array $filtros, string $orderBy = ''): Collection
    public function actualizarCargo(int $nroCargo, array $datos): bool
    public function crearCargoSac(int $nroCargo): array
}
```

## 📊 **Recursos Filament Planificados**

### **SacCargoResource**

```php
<?php

namespace App\Filament\Reportes\Resources;

class SacCargoResource extends Resource
{
    protected static ?string $model = null;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Reportes SAC';
    
    // Tabla con filtros avanzados
    // Exportación a Excel
    // Widgets de resumen
}
```

## 🧪 **Plan de Testing**

### **Unit Tests**

- [ ] `ModernCargoSacServiceTest`
- [ ] `SacRepositoryTest`  
- [ ] `VinculoCargoServiceTest`
- [ ] `Dl02ModelTest`
- [ ] `SacCargoDataTest`

### **Feature Tests**

- [ ] `SacCargoResourceTest`
- [ ] `SacReportExportTest`
- [ ] `SacCalculationIntegrationTest`

### **Performance Tests**

- [ ] `SacLargeDatasetTest`
- [ ] `SacQueryOptimizationTest`

## 📅 **Cronograma de Implementación**

### **Semana 1-2: Modelos Base**

- [x] ~~Análisis de estructura existente~~
- [x] ~~Implementación Dl02~~
- [ ] Implementación Dh12
- [ ] Implementación Dh21  
- [ ] Implementación Dh99
- [ ] Mejoras en Dh10

### **Semana 3: DTOs y Validación**

- [ ] Implementar SacCargoData
- [ ] Implementar SacLegajoData
- [ ] Implementar SacFiltrosData
- [ ] Validaciones con Laravel Data

### **Semana 4: Repository Pattern**

- [ ] Implementar SacRepository
- [ ] Implementar interfaces
- [ ] Migrar lógica de consultas

### **Semana 5: Services Modernos**

- [ ] Implementar ModernCargoSacService
- [ ] Implementar VinculoCargoService
- [ ] Inyección de dependencias
- [ ] Manejo de excepciones

### **Semana 6: Filament Integration**

- [ ] Implementar SacCargoResource
- [ ] Crear widgets de resumen
- [ ] Exportación a Excel
- [ ] Filtros avanzados

### **Semana 7-8: Testing y Optimización**

- [ ] Unit tests completos
- [ ] Feature tests
- [ ] Optimización de queries
- [ ] Performance testing
- [ ] Documentación

## 🎯 **Beneficios Esperados**

### **✅ Técnicos:**

- [x] **Mantenibilidad:** Código más limpio y fácil de mantener
- [x] **Testabilidad:** Inyección de dependencias permite testing unitario  
- [x] **Performance:** Uso de Eloquent optimizado y cache
- [x] **Escalabilidad:** Separación de responsabilidades
- [x] **Seguridad:** Validaciones estructuradas y tipos estrictos

### **✅ De Negocio:**

- [x] **Confiabilidad:** Menos bugs por validaciones mejoradas
- [x] **Velocidad:** Desarrollo más rápido de nuevas features
- [x] **Monitoreo:** Mejor logging y debugging
- [x] **Usabilidad:** Interfaces de Filament más intuitivas

## 📊 **Métricas de Progreso**

| Fase | Componentes | Completado | Pendiente | % Progreso |
|------|-------------|------------|-----------|------------|
| **Modelos** | 4 modelos | 1 | 3 | 25% |
| **DTOs** | 5 DTOs | 0 | 5 | 0% |
| **Repositories** | 3 repositories | 0 | 3 | 0% |
| **Services** | 4 services | 0 | 4 | 0% |
| **Resources** | 3 resources | 0 | 3 | 0% |
| **Tests** | 15 tests | 0 | 15 | 0% |

**Progreso General:** 🟡 **8%** (1/12 fases principales)

## 🔄 **Próximos Pasos Inmediatos**

### **Alta Prioridad:**

1. [ ] **Implementar Dh12** - Modelo de conceptos (crítico para SAC)
2. [ ] **Implementar Dh21** - Modelo de liquidaciones  
3. [ ] **Implementar Dh99** - Período fiscal actual
4. [ ] **Mejorar Dh10** - Agregar relaciones faltantes

### **Media Prioridad:**

5. [ ] **Crear SacCargoData** - DTO principal
6. [ ] **Implementar SacRepository** - Patrón repository
7. [ ] **Desarrollar ModernCargoSacService** - Servicio principal

### **Baja Prioridad:**

8. [ ] **Crear SacCargoResource** - Interface Filament
9. [ ] **Implementar tests** - Cobertura de testing
10. [ ] **Optimización** - Performance y cache

## 📚 **Referencias y Documentación**

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [FilamentPHP Documentation](https://filamentphp.com/docs)
- [Laravel Data Package](https://spatie.be/docs/laravel-data)
- [Repository Pattern in Laravel](https://asperbrothers.com/blog/implement-repository-pattern-in-laravel/)

## 🏷️ **Tags**

`#refactoring` `#laravel11` `#filament` `#sac` `#mapuche` `#legacy-code` `#modernization`

---

**Última actualización:** Diciembre 2024  
**Responsable:** Equipo de Desarrollo  

