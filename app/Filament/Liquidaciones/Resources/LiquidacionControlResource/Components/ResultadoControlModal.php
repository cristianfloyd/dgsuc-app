<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControlResource\Components;

use Filament\Forms;
use Filament\Tables;

class ResultadoControlModal extends Modal
{
    public $data;
    public $titulo;

    public function mount($data, $titulo)
    {
        $this->data = $data;
        $this->titulo = $titulo;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nro_legaj')
                ->label('Legajo'),
            Tables\Columns\TextColumn::make('nro_cargo')
                ->label('Cargo'),
            Tables\Columns\TextColumn::make('resultado')
                ->label('Resultado'),
        ];
    }
}
