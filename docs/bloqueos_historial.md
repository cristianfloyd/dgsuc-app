# 1. SERVICIOS PRINCIPALES

## 🔄 BloqueosHistorialService

* Responsabilidad única: Transferir bloqueos procesados al historial
* Mover de BloqueosDataModel a RepBloqueo
* Validar que estén procesados antes de transferir
* Mantener integridad referencial
* Generar metadatos de transferencia

```php
    // Procesamiento síncrono directo
    public function transferirAlHistorial(Collection $bloqueos): TransferResultData
    {
        DB::transaction(function() use ($bloqueos) {
            // Transferencia directa, 100-200 registros se procesan en <1 segundo
        });
    }
```

## 🧹 BloqueosCleanupService

* Responsabilidad única: Limpiar tabla de trabajo
* Eliminar solo registros ya transferidos al historial
* Validaciones de seguridad (no eliminar pendientes)
* Generar reportes de limpieza
* Mantener auditoría completa

```php
// Limpieza síncrona
public function limpiarTablaWork(array $periodoFiscal): CleanupResultData
{
    // Eliminación directa con validaciones
}
```

## 🎭 BloqueosArchiveOrchestratorService

* Responsabilidad única: Coordinar el proceso completo
* Orquestar historial + limpieza en secuencia
* Manejar transacciones distribuidas
* Generar reportes consolidados
* Manejo centralizado de errores

```php
    // Orquestación síncrona
    public function archivarPeriodoCompleto(array $periodoFiscal): ArchiveProcessData
    {
        // Ejecuta transferencia + limpieza en una sola transacción
        // Respuesta inmediata al usuario
    }
```

## 2. INTERFACES

```php
    interface BloqueosHistorialServiceInterface
    {
        public function transferirAlHistorial(Collection $bloqueos): TransferResultData;
        public function validarTransferencia(Collection $bloqueos): bool;
        public function getEstadisticasHistorial(array $periodoFiscal): array;
    }

    interface BloqueosCleanupServiceInterface  
    {
        public function limpiarTablaWork(array $periodoFiscal): CleanupResultData;
        public function validarLimpieza(array $periodoFiscal): bool;
        public function getRegistrosPendientes(?array $periodoFiscal = null): Collection;
    }

    interface BloqueosArchiveOrchestratorInterface
    {
        public function archivarPeriodoCompleto(array $periodoFiscal): ArchiveProcessData;
        public function validarArchivado(array $periodoFiscal): bool;
    }
```

## 3. DATA TRANSFER OBJECTS

```php
    TransferResultData   // Resultado de transferencia al historial (usa array periodoFiscal)
    CleanupResultData    // Resultado de limpieza  (usa array periodoFiscal)
    ArchiveProcessData   // Proceso completo de archivado (usa array periodoFiscal)
```

## 4. INTEGRACIÓN CON FILAMENT

* Header Actions (Nivel Tabla)

```php
    Action::make('archivar_periodo')
        ->label('Archivar Período Fiscal')
        ->icon('heroicon-o-archive-box')
        ->form([
            Select::make('periodo_fiscal')->required(),
            Checkbox::make('confirmar_archivado')->required()
        ])
        ->requiresConfirmation()
        ->modalHeading('¿Archivar período fiscal completo?')
        ->action(function ($data) {
            // Si el select retorna un string 'YYYYMM', convertirlo a array:
            $periodoFiscal = $data['periodo_fiscal'];
            $periodoFiscalArray = [
                'year' => (int) substr($periodoFiscal, 0, 4),
                'month' => (int) substr($periodoFiscal, 4, 2),
            ];
            $this->archivarPeriodo($periodoFiscalArray);
        })

    Action::make('limpiar_procesados')  
        ->label('Limpiar Procesados')
        ->icon('heroicon-o-trash')
        ->requiresConfirmation()
        ->action(fn() => $this->limpiarProcesados())
```

* Bulk Actions (Selección múltiple)

```php
    BulkAction::make('transferir_al_historial')
        ->label('Transferir Seleccionados al Historial')
        ->icon('heroicon-o-arrow-right-circle')
        ->requiresConfirmation()
        ->action(fn($records) => $this->transferirSeleccionados($records))
```

## 5. ESTRUCTURA DE ARCHIVOS PROPUESTA

```bash
    app/
    ├── Services/Reportes/
    │   ├── Interfaces/
    │   │   ├── BloqueosHistorialServiceInterface.php
    │   │   ├── BloqueosCleanupServiceInterface.php
    │   │   └── BloqueosArchiveOrchestratorInterface.php
    │   ├── BloqueosHistorialService.php
    │   ├── BloqueosCleanupService.php  
    │   └── BloqueosArchiveOrchestratorService.php
    ├── Data/Reportes/
    │   ├── TransferResultData.php
    │   ├── CleanupResultData.php
    │   └── ArchiveProcessData.php
    ├── Events/Bloqueos/ (Opcional)
    │   └── BloqueosArchivedEvent.php
    └── Policies/
        └── BloqueosArchivePolicy.php
```

## 6. FLUJO DE TRABAJO PROPUESTO

1. Usuario hace clic en "Archivar Período"
2. Validaciones inmediatas (sin jobs)
3. Transferencia síncrona a RepBloqueo (< 1 segundo)
4. Limpieza síncrona de BloqueosDataModel
5. Notificación inmediata con resultados
6. Evento opcional para auditoría
