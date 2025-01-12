# Procedimiento de Importación de Excel a PostgreSQL con FilamentPHP

## Descripción General

Este documento describe el proceso de implementación para importar archivos Excel a una base de datos PostgreSQL utilizando Laravel 11, PHP 8.3 y FilamentPHP 3.

## Requisitos Previos

- PHP 8.3 o superior
- Laravel 11
- PostgreSQL
- Composer
- FilamentPHP 3

## 1. Instalación de Dependencias

Ejecutar los siguientes comandos en la terminal del proyecto:

```bash
composer require maatwebsite/excel
composer require filament/spatie-laravel-media-library-plugin
```

## 2. Estructura del Proyecto

### 2.1 Modelo de Datos

Ubicación: app/Models/ImportData.php

Define la estructura de datos
Gestiona las columnas permitidas para importación

### 2.2 Clase de Importación

Ubicación: app/Imports/BloqueosImport.php

Maneja la lógica de importación
Mapea las columnas del Excel a la base de datos

### 2.3 Recurso Filament

Ubicación: app/Filament/Resources/ImportDataResource.php

Configura la interfaz de usuario
Define los campos y validaciones

### 2.4 Migración de Base de Datos

Ubicación: database/migrations/[timestamp]_create_import_data_table.php

Establece la estructura de la tabla en PostgreSQL

## 3. Proceso de Implementación

Crear la Migración

Ejecutar: php artisan make:migration create_import_data_table
Definir la estructura de la tabla
Aplicar la migración con php artisan migrate
Configurar el Modelo

Crear el modelo ImportData
Definir los campos fillable
Establecer las relaciones necesarias
Implementar la Importación

Crear la clase BloqueosImport
Configurar el mapeo de columnas
Implementar la lógica de importación
Configurar FilamentPHP

Crear el recurso ImportDataResource
Definir los formularios y tablas
Implementar las acciones de importación

## 4. Uso del Sistema

Acceder al panel de administración de Filament
Navegar a la sección de importación
Seleccionar el archivo Excel
Confirmar la importación
Verificar los datos importados en la tabla

## 5. Validaciones y Restricciones

Formatos aceptados: .xls, .xlsx
Tamaño máximo de archivo: [especificar]
Estructura requerida del Excel:
Columna 1: [especificar]
Columna 2: [especificar]
Columna 3: [especificar]

## 6. Manejo de Errores

Errores Comunes

Formato de archivo inválido
Estructura incorrecta del Excel
Datos duplicados
Soluciones

Verificar el formato del archivo
Validar la estructura del Excel
Revisar los logs de error

## 7. Flujo de trabajo

Excel -> DTO -> Servicios -> Modelo -> Base de Datos

- Excel -> Array
- Array -> BloqueosData (DTO)
- DTO -> Procesamiento
- Procesamiento -> Collection

## 8. Soporte

Para soporte técnico contactar a: [carenas@uba.ar]

Administrador del Sistema: [carenas@uba.ar]

## 9. Control de Versiones

Versión Fecha Autor Cambios
1.0 [fecha] [arca] Versión inicial
