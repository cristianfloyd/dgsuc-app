# Refactor Clase SicossLegacy

## Repositorios Propuestos

### 1. LicenciaRepository ✅ COMPLETADO

* Responsabilidad: Gestión de consultas relacionadas con licencias
* Métodos migrados:
* ✅ get_licencias_protecintegral_vacaciones() → getLicenciasProtecintegralVacaciones()
* ✅ get_licencias_vigentes() → getLicenciasVigentes()
* ✅ Consultas relacionadas con estados de licencias y períodos

### 2. Dh03Repository (CargoRepository) ✅ COMPLETADO

* Responsabilidad: Gestión de cargos activos y su estado
* Métodos migrados:
* ✅ get_cargos_activos_sin_licencia() → getCargosActivosSinLicencia()
* ✅ get_cargos_activos_con_licencia_vigente() → getCargosActivosConLicenciaVigente()
* ✅ get_limites_cargos() → getLimitesCargos()

### 3. Dh21Repository (ConceptoLiquidadoRepository) ✅ PARCIALMENTE COMPLETADO

* Responsabilidad: Gestión de conceptos liquidados y su procesamiento
* Métodos migrados:
* ✅ obtener_conceptos_liquidados() → obtenerConceptosLiquidadosSicoss()
* ✅ obtener_periodos_retro() → obtenerPeriodosRetro()
* 🔄 consultar_conceptos_liquidados() → convertido a instance method
* 🔄 sumarizar_conceptos_por_tipos_grupos() → convertido a instance method
* ⏳ calcular_remuner_grupo() → PENDIENTE DE EXTRAER

### 4. Dh01Repository (LegajoRepository) ✅ PARCIALMENTE COMPLETADO

* Responsabilidad: Gestión de datos de legajos para SICOSS
* Métodos migrados:
* ✅ get_sql_legajos() → getSqlLegajos()
* 🔄 obtener_legajos() → convertido a instance method
* ⏳ Consultas relacionadas con datos básicos de empleados

### 5. SicossCalculoRepository ✅ COMPLETADO

* Responsabilidad: Cálculos específicos del sistema SICOSS
* Métodos migrados:
* ✅ calculo_horas_extras() → calculoHorasExtras()
* ✅ otra_actividad() → otraActividad()
* ✅ codigo_os() → codigoOs()
* ✅ calcular_remuner_grupo() → calcularRemunerGrupo()
* 🔄 calcularSACInvestigador() → convertido a instance method

### 6. PeriodoFiscalRepository ✅ COMPLETADO (migrado a Dh21Repository)

* Responsabilidad: Gestión de períodos fiscales y retroactivos
* Métodos migrados:
* ✅ obtener_periodos_retro() → obtenerPeriodosRetro() (en Dh21Repository)

## Estructura Propuesta

```bash
app/Repositories/Sicoss/
├── LicenciaRepository.php
├── CargoRepository.php
├── ConceptoLiquidadoRepository.php
├── LegajoRepository.php
├── SicossCalculoRepository.php
├── PeriodoFiscalRepository.php
└── Contracts/
    ├── LicenciaRepositoryInterface.php
    ├── CargoRepositoryInterface.php
    ├── ConceptoLiquidadoRepositoryInterface.php
    ├── LegajoRepositoryInterface.php
    ├── SicossCalculoRepositoryInterface.php
    └── PeriodoFiscalRepositoryInterface.php
```

