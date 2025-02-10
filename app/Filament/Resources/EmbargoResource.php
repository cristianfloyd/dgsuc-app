<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;
use Livewire\Attributes\On;
use App\Tables\EmbargoTable;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use App\Models\EmbargoProcesoResult;
use App\Services\EmbargoTableService;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use App\Traits\DisplayResourceProperties;
use App\Filament\Resources\EmbargoResource\Pages;


class EmbargoResource extends Resource
{
    use DisplayResourceProperties;

    protected static ?string $model = EmbargoProcesoResult::class;
    protected static ?string $navigationGroup = 'Liquidaciones';
    protected static ?string $modelLabel = 'Embargo';
    protected static ?string $slug = 'embargos';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected EmbargoTable $embargoTable;



    // Propiedades públicas del recurso
    public array $periodoFiscal = [];
    public int $nroLiquiProxima = 9;
    public array $nroComplementarias = [];
    public int $nroLiquiDefinitiva = 1;
    public bool $insertIntoDh25 = false;
    protected EmbargoTableService $tableService;



    public function boot(EmbargoTableService $tableService): void
    {
        $this->tableService = $tableService;
        $this->ensureTableExists();
    }

    public function mount(EmbargoTable $embargoTable): void
    {
        $this->embargoTable = $embargoTable;
        $this->nroLiquiProxima = 4; // Lógica para determinar este valor
        $this->nroComplementarias = []; // Lógica para determinar este array
        $this->nroLiquiDefinitiva = 0; // Lógica para determinar este valor
        $this->insertIntoDh25 = false; // Lógica para determinar este booleano

        // Usar estas variables para actualizar los datos
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')->label('Nro. Liquidación'),
                TextColumn::make('tipo_embargo')->label('Tipo Embargo'),
                TextColumn::make('nro_legaj')->label('Nro. Legajo'),
                TextColumn::make('remunerativo')->money('ARS'),
                TextColumn::make('no_remunerativo')->money('ARS'),
                TextColumn::make('total')->money('ARS'),
                TextColumn::make('codn_conce')->label('Código Concepto'),
            ])
            ->defaultSort('nro_legaj')
            ->filters([
                //
            ])
            ->actions([
                Action::make('Procesar')
                    ->label('Actualizar Datos')
                    ->action(fn() => self::actualizarDatos())
                    ->button(),
                Action::make('configure')
                    ->label('Configure Parameters')
                    ->url(fn(): string => static::getUrl('configure'))
                    ->icon('heroicon-o-cog')
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function actualizarDatos(array $data = []): void
    {


        $instance = new static();
        $instance->setPropertyValues($data);


        // Actualizamos las propiedades del recurso
        $instance->nroLiquiProxima = $data['nroLiquiProxima'] ?? 0;
        $instance->nroComplementarias = $data['nroComplementarias'] ?? [];
        $instance->nroLiquiDefinitiva = $data['nroLiquiDefinitiva'] ?? 0;
        $instance->insertIntoDh25 = $data['insertIntoDh25'] ?? false;


        log::debug('Datos actualizados correctamente', $data);

        // Opcional: Mostrar un mensaje de éxito
        Notification::make()
            ->title('Datos actualizados correctamente')
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmbargos::route('/'),
            'dashboard' => Pages\DashboardEmbargo::route('/admin'),
            'configure' => Pages\ConfigureEmbargoParameters::route('/configure'),
        ];
    }



    public static function getActions(): array
    {
        return [
            Action::make('actualizar')
                ->label('Actualizar Datos')
                ->action(fn() => self::actualizarDatos())
                ->button(),
        ];
    }



    /**
     * Obtiene las propiedades a mostrar y sus valores actuales.
     * Este método utiliza la implementación del trait, que maneja el caché.
     *
     * @return array
     */
    public function getPropertiesToDisplay(): array
    {
        // método del trait que maneja el caché
        return $this->getCachedProperties();
    }


    #[On('updated-periodo-fiscal')]
    public function updatedPeriodoFiscal(array $periodoFiscal): void
    {
        $this->periodoFiscal = $periodoFiscal;
        Notification::make('Periodo Fiscal Actualizado');

    }



    protected function getListeners(): array
    {
        return [
            'propertiesUpdated' => 'handlePropertiesUpdated',
        ];
    }

    /**
     * Define las propiedades por defecto del recurso.
     * Este método es requerido por el trait DisplayResourceProperties.
     *
     * @return array
     */
    protected function getDefaultProperties(): array
    {
        return [
            'nroLiquiProxima' => $this->nroLiquiProxima,
            'nroComplementarias' => $this->nroComplementarias,
            'nroLiquiDefinitiva' => $this->nroLiquiDefinitiva,
            'insertIntoDh25' => $this->insertIntoDh25,
        ];
    }
}
