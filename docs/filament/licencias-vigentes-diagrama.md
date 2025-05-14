# Diagrama de Arquitectura: Consulta de Licencias Vigentes

Este documento presenta un diagrama de la arquitectura y flujo de datos implementado para la consulta de licencias vigentes.

## Diagrama de Componentes y Flujo

```bash
┌─────────────────────────────────────────────────────────────────────────┐
│                            INTERFAZ DE USUARIO                           │
│  ┌──────────────────────────────┐      ┌──────────────────────────────┐ │
│  │ LicenciaVigenteResource      │      │ ListLicenciaVigentes         │ │
│  │ (Filament Resource)          │◄────►│ (Filament ListRecords Page)  │ │
│  └───────────────┬──────────────┘      └──────────────────────────────┘ │
└─────────────────┬─────────────────────────────────────────────────────┬─┘
                  │                                                     │
                  │ getTableRecords()                                   │ Excel download
                  ▼                                                     ▼
┌─────────────────────────────────┐           ┌───────────────────────────────┐
│ LicenciaService                 │           │ LicenciasVigentesExport       │
│ (Service Layer)                 │           │ (Excel Export)                │
│                                 │           │                               │
│ - getLicenciasVigentes(legajos) │───┐       │ - collection()                │
└─────────────────┬───────────────┘   │       │ - map($row)                   │
                  │                   │       │ - headings()                  │
                  │ Database Query    │       └───────────────┬───────────────┘
                  ▼                   │                       │
┌─────────────────────────────────┐   │                       │
│ MapucheConfig                   │   │  Transform            │
│ (Configuration Helper)          │   │  to DTOs              │
│                                 │   │                       │ Uses
│ - getFechaInicioPeriodoActual() │   │                       │ DTOs
│ - getVarLicencias10Dias()       │◄──┘                       │
└─────────────────┬───────────────┘                           │
                  │                                           │
                  │ Parameter Values                          │
                  ▼                                           │
┌─────────────────────────────────┐                           │
│ Rrhhini                         │                           │
│ (Database Access)               │                           │
└─────────────────────────────────┘                           │
                  ▲                                           │
                  │ SQL Queries                               │
                  │                                           │
┌─────────────────────────────────┐                           │
│ DATABASE MAPUCHE                │◄──────────────────────────┘
└─────────────────────────────────┘
```

## Explicación del Flujo

1. **Interacción de Usuario**:
   - El usuario accede a la página de licencias vigentes
   - Ingresa números de legajo a consultar
   - Submit del formulario

2. **Procesamiento en Filament**:
   - `LicenciaVigenteResource` almacena los legajos en la sesión
   - Se ejecuta `getTableRecords()` para obtener datos
   - Se llama al `LicenciaService`

3. **Capa de Servicio**:
   - `LicenciaService` obtiene parámetros a través de `MapucheConfig`
   - Construye y ejecuta consulta SQL contra la base Mapuche
   - Transforma resultados en colección de objetos `LicenciaVigenteData` (DTOs)

4. **Presentación**:
   - Filament renderiza los datos en una tabla interactiva
   - El usuario puede filtrar, ordenar y exportar datos

5. **Exportación (opcional)**:
   - Al solicitar exportación, se instancia `LicenciasVigentesExport`
   - Se genera archivo Excel con formato personalizado

## Anotaciones Técnicas

- La arquitectura implementa un patrón Repository+Service+DTO
- Las consultas SQL están parametrizadas para evitar SQL Injection
- Se utiliza la sesión para almacenar parámetros de la consulta actual
- La conversión a `EloquentCollection` asegura compatibilidad con Filament
