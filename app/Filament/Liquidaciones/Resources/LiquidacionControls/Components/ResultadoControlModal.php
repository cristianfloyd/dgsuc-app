<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControls\Components;

use Filament\Support\View\Components\ModalComponent;
use Filament\Tables\Columns\TextColumn;

class ResultadoControlModal extends ModalComponent
{
    public $data;

    public $titulo;

    public function mount($data, $titulo): void
    {
        $this->data = $data;
        $this->titulo = $titulo;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nro_legaj')
                ->label('Legajo'),
            TextColumn::make('nro_cargo')
                ->label('Cargo'),
            TextColumn::make('resultado')
                ->label('Resultado'),
        ];
    }
}
