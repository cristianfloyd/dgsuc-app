<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use App\Exports\ChunkedReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Filament\Notifications\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateExcelExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hora de timeout
    public $tries = 1; // No reintentar si falla

    protected $modelClass;
    protected $filters;
    protected $filepath;
    protected $userId;

    public function __construct(string $modelClass, array $filters, string $filepath, int $userId)
    {
        $this->modelClass = $modelClass;
        $this->filters = $filters;
        $this->filepath = $filepath;
        $this->userId = $userId;
    }

    public function handle()
    {
        // Crear la consulta base
        $query = $this->modelClass::query();

        // Aplicar los filtros
        foreach ($this->filters as $filter => $value) {
            // Implementar lógica para aplicar los filtros según tu estructura
            // Esto dependerá de cómo están estructurados tus filtros
        }

        // Seleccionar solo las columnas necesarias
        $query = $query->select([
            'nro_liqui',
            'desc_liqui',
            'apellido',
            'nombre',
            'cuil',
            'nro_legaj',
            'nro_cargo',
            'codc_uacad',
            'codn_conce',
            'impp_conce'
        ]);

        // Generar el Excel usando chunks para optimizar memoria
        Excel::store(
            new ChunkedReportExport($query),
            $this->filepath,
            'local'
        );

        // Notificar al usuario que el archivo está listo
        $user = User::find($this->userId);

        Notification::make()
            ->title('Excel generado correctamente')
            ->body('Tu archivo de exportación está listo para descargar.')
            ->actions([
                Action::make('download')
                    ->label('Descargar')
                    ->url(Storage::url($this->filepath))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase($user);
    }
}