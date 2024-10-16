<?php

namespace App\Tables;

use App\Models\EmbargoProcesoResult;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmbargoTable
{
    private ?array $nroComplementarias;
    private ?int $nroLiquiDefinitiva;
    private ?int $nroLiquiProxima;
    private ?bool $insertIntoDh25;

    public function __construct(
        ?array $nroComplementarias = null,
        ?int   $nroLiquiDefinitiva = null,
        ?int   $nroLiquiProxima = null,
        ?bool  $insertIntoDh25 = null
    )
    {
        $this->nroComplementarias = $nroComplementarias;
        $this->nroLiquiDefinitiva = $nroLiquiDefinitiva;
        $this->nroLiquiProxima = $nroLiquiProxima;
        $this->insertIntoDh25 = $insertIntoDh25;
    }


    public function table(Table $table): Table
    {
        if ($this->nroComplementarias === null || $this->nroLiquiDefinitiva === null || $this->nroLiquiProxima === null) {
            return $table->query(fn() => new Collection());
        }
        return $table
            ->query(function (): Builder {
                if ($this->nroComplementarias === null || $this->nroLiquiDefinitiva === null || $this->nroLiquiProxima === null) {
                    return EmbargoProcesoResult::query()->whereRaw('1 = 0');
                }

                return EmbargoProcesoResult::executeEmbargoProcesoQuery(
                    $this->nroComplementarias,
                    $this->nroLiquiDefinitiva,
                    $this->nroLiquiProxima,
                    $this->insertIntoDh25 ?? false
                );
            })
            ->columns([
                TextColumn::make('nro_liqui')->label('Número de Liquidación'),
                TextColumn::make('tipo_embargo')->label('Tipo de Embargo'),
                TextColumn::make('nro_legaj')->label('Número de Legajo'),
                TextColumn::make('remunerativo')->label('Monto Remunerativo'),
                TextColumn::make('no_remunerativo')->label('Monto No Remunerativo'),
                TextColumn::make('total')->label('Total'),
                TextColumn::make('codn_conce')->label('Código de Concepto'),
            ]);
    }
}
