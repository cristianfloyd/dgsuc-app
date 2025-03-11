# Plan de Implementación: Exportación Excel Optimizada

## 1. Configuración del Sistema de Colas

- [ ] Configurar el driver de cola en `.env` (database o redis)

```php
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database
```

- [ ] Ejecutar migraciones para crear tablas de colas

```bash
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

- [ ] Configurar supervisor para entorno de producción

```bash
# Crear archivo de configuración
sudo nano /etc/supervisor/conf.d/laravel-worker.conf

# Contenido del archivo
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600

# Reiniciar supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 2. Crear Clases para Exportación

- [ ] Crear exportador optimizado para grandes volúmenes

```bash
php artisan make:export ChunkedReportExport --model=ConceptoListado
```

- [ ] Implementar la clase de exportación con procesamiento por chunks

```php
# app/Exports/ChunkedReportExport.php
```

## 3. Crear Job para Procesamiento en Segundo Plano

- [ ] Crear el job para generar el Excel

```bash
php artisan make:job GenerateExcelExportJob
```

- [ ] Implementar la lógica del job

```php
# app/Jobs/GenerateExcelExportJob.php
```

## 4. Configurar Almacenamiento para Archivos Exportados

- [ ] Configurar disco de almacenamiento en `config/filesystems.php`

```php
'disks' => [
    // ...
    'exports' => [
        'driver' => 'local',
        'root' => storage_path('app/public/exports'),
        'url' => env('APP_URL').'/storage/exports',
        'visibility' => 'public',
    ],
],
```

- [ ] Crear enlace simbólico para acceso público

```bash
php artisan storage:link
```

- [ ] Crear directorio para almacenar exportaciones

```bash
mkdir -p storage/app/public/exports
chmod -R 775 storage/app/public/exports
```

## 5. Modificar el Controlador de Filament

- [ ] Actualizar la acción de exportación en `ListReporteConceptoListados.php`

```php
# app/Filament/Resources/ReporteConceptoListadoResource/Pages/ListReporteConceptoListados.php
```

## 6. Implementar Sistema de Notificaciones

- [ ] Configurar notificaciones de Filament para informar al usuario

```php
# Notificación cuando se inicia la exportación
# Notificación cuando la exportación está completa
```

- [ ] Crear tabla de notificaciones en la base de datos (si no existe)

```bash
php artisan notifications:table
php artisan migrate
```

## 7. Implementar Limpieza de Archivos Antiguos

- [ ] Crear comando para limpiar archivos antiguos

```bash
php artisan make:command CleanupExportFiles
```

- [ ] Implementar lógica del comando

```php
# app/Console/Commands/CleanupExportFiles.php
```

- [ ] Programar el comando en el kernel

```php
# app/Console/Kernel.php
```

## 8. Pruebas y Optimización

- [ ] Probar la exportación con conjuntos pequeños de datos
- [ ] Probar la exportación con conjuntos grandes de datos (150k registros)
- [ ] Monitorear uso de memoria y tiempo de ejecución
- [ ] Ajustar parámetros de chunks y timeout según sea necesario

## 9. Documentación

- [ ] Documentar el proceso de exportación para el equipo
- [ ] Crear instrucciones para configurar workers en diferentes entornos
- [ ] Documentar proceso de solución de problemas comunes

## 10. Mejoras Opcionales (Para Futuras Iteraciones)

- [ ] Implementar sistema de progreso para exportaciones muy grandes
- [ ] Añadir opciones para personalizar columnas exportadas
- [ ] Implementar exportación en diferentes formatos (CSV, PDF)
- [ ] Crear panel de administración para ver/gestionar exportaciones en curso
