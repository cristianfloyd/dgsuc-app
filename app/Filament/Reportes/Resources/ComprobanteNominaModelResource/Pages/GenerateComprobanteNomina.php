<?php

namespace App\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Mapuche\Dh22;
use App\Services\CheFileGenerator;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use App\Traits\MapucheConnectionTrait;
use Filament\Notifications\Notification;
use App\Filament\Reportes\Resources\ComprobanteNominaModelResource;

class GenerateComprobanteNomina extends Page
{
    use MapucheConnectionTrait;
    protected static string $resource = ComprobanteNominaModelResource::class;
    protected static string $view = 'filament.resources.comprobante-nomina.pages.generate';
    protected static ?string $title = 'Generar Comprobantes';
    protected $connection;
    public ?array $liquidaciones = [];
    public ?int $anio = null;
    public ?int $mes = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->connection = $this->getConnectionFromTrait();

    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('liquidaciones')
                            ->label('Liquidaciones')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(fn() => Dh22::getLiquidacionesForWidget()
                                ->get()
                                ->pluck('desc_liqui', 'nro_liqui')
                            )
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!empty($state)) {
                                    $liquidacion = Dh22::query()->where('nro_liqui', $state[0])->first();
                                    $set('anio', $liquidacion->per_liano);
                                    $set('mes', $liquidacion->per_limes);
                                }
                            }),

                        Select::make('anio')
                            ->label('Año')
                            ->required()
                            ->disabled()
                            ->options(fn() => array_combine(
                                range(date('Y')-5, date('Y')),
                                range(date('Y')-5, date('Y'))
                            )),

                        Select::make('mes')
                            ->label('Mes')
                            ->required()
                            ->disabled()
                            ->options(fn() => collect(range(1, 12))->mapWithKeys(
                                fn($mes) => [$mes => nombreMes($mes)]
                            )->toArray()),
                    ])
            ]);
    }

    public function generate(): void
    {
        $this->connection = $this->getConnectionFromTrait();
        Log::info('ComprobanteNominaResource.php', [
            'liquidaciones' => $this->form->getState()['liquidaciones'],
                'anio' => $this->anio,
                'mes' =>  $this->mes,
            ]);

        try {
            $this->connection->beginTransaction();

            $formData = $this->form->getState();
            $generator = new CheFileGenerator();

            // Correct way to access the first liquidation from the array
            $nroLiqui = (int) $formData['liquidaciones'][0];

            $generator->setNroLiqui($nroLiqui);

            $comprobantes = $generator->processAndStore(
                [$nroLiqui],
                $this->anio,
                $this->mes
            );

            $this->connection->commit();

            Notification::make()
                ->title('Procesamiento exitoso')
                ->body("Se procesaron {$comprobantes->count()} comprobantes")
                ->success()
                ->send();

            $this->redirect(ComprobanteNominaModelResource::getUrl('index'));

        } catch (\Throwable $e) {
            $this->connection->rollBack();
            Log::error($e->getMessage());
            Notification::make()
                ->title('Error en el procesamiento')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
