<?php

declare(strict_types=1);

namespace App\Filament\Liquidaciones\Resources\Dh21Resource\Pages;

use Filament\Forms\Get;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use App\Models\Mapuche\Dh22;
use App\Services\Dh21Service;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Filament\Widgets\IdLiquiSelector;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Liquidaciones\Resources\Dh21Resource;

class ConceptosTotales extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = Dh21Resource::class;

    protected static string $view = 'filament.resources.dh21-resource.pages.conceptos-totales';
    protected ?int $nro_liqui = null;
    protected ?int $codn_fuent = null;
    protected string $total = 'Total';
    protected ?int $lastNroLiqui = null;
    protected ?string $codigoEscalafon = null;


    public function table(Table $table): Table
    {
        return $table
            ->query($this->updateQuery())
            ->columns([
                TextColumn::make('id_liquidacion')->hidden(),
                TextColumn::make('codn_conce')
                    ->label('Código de Concepto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_impp')
                    ->label(
                        function (Get $get){
                            return $this->total;
                        }
                    )
                    ->money('ARS')
                    ->sortable(),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            IdLiquiSelector::class,
        ];
    }


    /**
     * Obtiene una consulta de Dh21 que filtra los conceptos totales por el número de liquidación proporcionado.
     *
     * @param int|null $nro_liqui El número de liquidación a filtrar, o null para obtener todos los conceptos.
     * @return \Illuminate\Database\Eloquent\Builder La consulta de Dh21 filtrada por el número de liquidación.
     */
    public function updateQuery($nro_liqui = null):Builder
    {
        try {
            if ($this->lastNroLiqui == null) {
                $this->lastNroLiqui = Dh22::getLastIdLiquidacion();
                $this->nro_liqui = $this->lastNroLiqui;
                return app(Dh21Service::class)->conceptosTotales($this->nro_liqui);
            }
            return app(Dh21Service::class)->conceptosTotales($nro_liqui);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return app(Dh21Service::class)->conceptosTotales($this->nro_liqui);
        }
    }


    #[On('idLiquiSelected')]
    public function updateTable($nro_liqui = null, $desc_liqui = null): void
    {
        $this->nro_liqui = $nro_liqui;
        $this->total = Dh22::getDescripcionLiquidacion($nro_liqui);
        Log::info("nro_liqui: $nro_liqui");
        Log::info("desc_liqui: $this->total");
        $this->table->query($this->updateQuery($nro_liqui));
    }


    /**
     * Filtra los conceptos totales de la tabla por el código de escalafón proporcionado.
     *
     * @param string|null $codigoEscalafon El código de escalafón a filtrar, o null para obtener todos los conceptos.
     * @return void
     */
    public function filterByCodigoEscalafon(string $codigoEscalafon = null): void
    {
        $this->codigoEscalafon = $codigoEscalafon;
        $this->table->query(
            $this->updateQuery($this->nro_liqui)
                ->where('codigoescalafon', '=', $codigoEscalafon)
        );
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }
}
