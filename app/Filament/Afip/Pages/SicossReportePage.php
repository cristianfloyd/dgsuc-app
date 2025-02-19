<?php

namespace App\Filament\Afip\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\SicossReporte as SicossReporteModel;

class SicossReportePage extends Page implements \Filament\Tables\Contracts\HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Reporte SICOSS';
    protected static ?string $title = 'Reporte SICOSS';
    protected static ?string $navigationGroup = 'AFIP';

    public $anio;
    public $mes;

    protected static string $view = 'filament.pages.sicoss-reporte';

    public function mount(): void
    {
        $this->anio = date('Y');
        $this->mes = date('n');
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('anio')
                            ->label('Año')
                            ->options([
                        '2024' => '2024',
                        '2025' => '2025',
                    ])
                    ->default(date('Y'))
                    ->live()
                    ->afterStateUpdated(function($state){
                        $this->anio = $state;
                        $this->table->query(fn () => SicossReporteModel::query()->getReporte($this->anio, $this->mes));
                    }),
                Select::make('mes')
                    ->label('Mes')
                    ->options([
                        '1' => 'Enero',
                        '2' => 'Febrero',
                        '3' => 'Marzo',
                        '4' => 'Abril',
                        '5' => 'Mayo',
                        '6' => 'Junio',
                        '7' => 'Julio',
                        '8' => 'Agosto',
                        '9' => 'Septiembre',
                        '10' => 'Octubre',
                        '11' => 'Noviembre',
                        '12' => 'Diciembre',
                    ])
                    ->default(date('n'))
                    ->live()
                    ->afterStateUpdated(function($state){
                        $this->mes = $state;
                        $query = SicossReporteModel::query()->getReporte($this->anio, $this->mes);
                        Log::info($query->getBindings());
                        $this->table->query($query);
                    }),
                    ])

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SicossReporteModel::query()->getReporte($this->anio, $this->mes))
            ->columns([
                TextColumn::make('nro_liqui')
                    ->label('Nro Liquidación')
                    ->sortable(),
                TextColumn::make('desc_liqui')
                    ->label('Descripción')
                    ->searchable(),
                TextColumn::make('remunerativo')
                    ->label('Remunerativo')
                    ->money('ARS'),
                TextColumn::make('no_remunerativo')
                    ->label('No Remunerativo')
                    ->money('ARS'),
                TextColumn::make('aportesijpdh21')
                    ->label('Aportes SIJP')
                    ->money('ARS'),
                TextColumn::make('aporteinssjpdh21')
                    ->label('Aportes INSSJP')
                    ->money('ARS'),
                TextColumn::make('contribucionsijpdh21')
                    ->label('Contribución SIJP')
                    ->money('ARS'),
                TextColumn::make('contribucioninssjpdh21')
                    ->label('Contribución INSSJP')
                    ->money('ARS'),
            ])
            ->defaultSort('nro_liqui');
    }
}
