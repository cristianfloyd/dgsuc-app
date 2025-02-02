# Guía de Generación Archivo CHE

## 1. Métodos Utilitarios

### 1.1 Formateo de Datos
- **llena_importes**: Rellena valores numéricos con ceros a la izquierda
- **llena_blancos**: Rellena texto con espacios a la derecha
- **llena_blancos_izq**: Rellena texto con espacios a la izquierda

## 2. Proceso Principal

### 2.1 Obtención de Datos Base
- Crea tabla temporal 'l' con datos base
- Procesa diferentes tipos de conceptos (C, D, F, etc.)
- Incluye cálculos de netos por tipo de concepto

### 2.2 Estructura de Datos por Tipo de Concepto
Cada tipo de concepto (C, D, F) requiere:
1. Fuente de financiamiento
2. Imputación presupuestaria
3. Datos del legajo y cargo
4. Cálculo de inciso
5. Suma de importes

### 2.3 Tablas Temporales Generadas
- **importes_netos_c**: Netos tipo C
- **importes_netos_f**: Netos tipo F
- **importes_netos_d**: Netos tipo D
- Tabla principal 'l'

## 3. Joins Principales

### 3.1 Tablas Relacionadas
- dh21/imp_liquidaciones: Tabla principal de liquidaciones
- dh22: Datos económicos
- dh01: Datos de legajos
- dh17: Conceptos
- dh03: Cargos
- dh11: Categorías
- dh31: Dedicaciones
- dh35: Escalafones y características

### 3.2 Condiciones de Join
- Liquidación específica
- nro_orimp > 0
- codn_conce > 0

## 4. Campos Críticos para CHE

### 4.1 Datos Requeridos
- codn_area
- codn_subar
- tipo_conce
- impp_conce (importe)
- codn_grupo
- desc_grupo

### 4.2 Agrupaciones
- Por área y subárea
- Por tipo de concepto
- Por grupo presupuestario

## 5. Consideraciones de Implementación

### 5.1 Optimizaciones Sugeridas
1. Usar índices para joins frecuentes
2. Implementar procesamiento por lotes
3. Considerar particionamiento por tipo_conce
4. Usar transacciones para operaciones en tablas temporales

### 5.2 Validaciones
1. Verificar existencia de liquidación
2. Validar importes
3. Verificar integridad de datos relacionados
4. Controlar totales por tipo de concepto

### 5.3 Manejo de Errores
1. Control de tablas temporales existentes
2. Validación de datos requeridos
3. Manejo de excepciones en cálculos
4. Logging de operaciones críticas
