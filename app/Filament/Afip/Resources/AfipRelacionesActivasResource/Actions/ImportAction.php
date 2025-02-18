<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivasResource\Actions;

use App\Models\Mapuche\Dh22;
use App\Models\UploadedFile;
use Filament\Actions\Action;
use App\Services\ColumnMetadata;
use App\Services\DatabaseService;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use App\Services\FileProcessorService;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;

class ImportAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'import';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Importar TXT')
            ->icon('heroicon-o-arrow-up-tray')
            ->form([
                TextInput::make('periodo_fiscal')
                    ->label('Periodo Fiscal')
                    ->required()
                    ->length(6)
                    ->placeholder('YYYYMM')
                    ->default(date('Ym'))
                    ->helperText('Formato: YYYYMM (ejemplo: 202401)'),
                Select::make('nro_liqui')
                    ->label('Liquidación')
                    ->options(function () {
                        return Dh22::query()
                            ->definitiva()
                            ->orderBy('nro_liqui', 'desc')
                            ->limit(12)
                            ->get()
                            ->mapWithKeys(function ($liquidacion) {
                                return [
                                    $liquidacion->nro_liqui => "#{$liquidacion->nro_liqui} - {$liquidacion->desc_liqui}"
                                ];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Seleccione una liquidación')
                    ->helperText('Seleccione la liquidación definitiva correspondiente'),

                FileUpload::make('file')
                    ->label('Archivo TXT')
                    ->acceptedFileTypes(['text/plain'])
                    ->maxSize(25120)
                    ->disk('local')
                    ->directory('afiptxt')
                    ->visibility('public')
                    ->storeFileNamesIn('original_filename')
                    ->required()
            ])
            ->action(function (array $data): void {
                try {
                    // Dentro del método de acción:
                    $file = $data['file'];
                    $filePath = $file;
                    $filename = basename($filePath);
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $timenow = time();
                    $newFilename = pathinfo($filename, PATHINFO_FILENAME) . "-{$timenow}.{$extension}";

                    // Filament ya movió el archivo a almacenamiento, solo necesitamos la ruta
                    $filePath = Storage::disk('public')->path($file);


                    // periodo fiscal y nro_liqui
                    $periodoFiscal = $data['periodo_fiscal'];
                    $nro_liqui = $data['nro_liqui'];



                    $uploadedFile = UploadedFile::create([
                        'periodo_fiscal' => $data['periodo_fiscal'],
                        'origen' => 'afip',
                        'filename' => $newFilename,
                        'original_name' => $filename,
                        'file_path' => $file,
                        'user_name' => 'arca', //auth()->user()->name,
                        'user_id' => 1, //auth()->user()->id,
                        'nro_liqui' => $nro_liqui,
                    ]);

                    // Instanciamos los servicios necesarios
                    $fileProcessor = new FileProcessorService(
                        new DatabaseService(),
                        new ColumnMetadata(),
                        app()->make('App\Contracts\DataMapperInterface'),
                        $periodoFiscal
                    );


                    // Procesar el archivo usando el servicio FileProcessor
                    $fileProcessor = app(FileProcessorService::class);
                    $processedData = $fileProcessor->handleFileImport($uploadedFile, 'afip');


                    $databaseService = app(DatabaseService::class);
                    $result = $databaseService->insertBulkData($processedData, 'afip_relaciones_activas');


                    if ($result['success']) {
                        Notification::make()
                            ->title('Archivo importado correctamente')
                            ->body("Se importaron {$result['data']['rowsInserted']} registros")
                            ->success()
                            ->send();
                    } else {
                        throw new \Exception($result['message']);
                    }
                } catch (\Exception $e) {
                    Log::error('Error en importación: ' . $e->getMessage());

                    Notification::make()
                        ->title('Error al importar archivo')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
