<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use App\Filament\Resources\ReporteResource\Pages;
use Filament\Pages\Page;

class ReporteResource extends Page
{
    protected static ?string $model = null;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }


    public function getEloquentQuery()
    {
        // Sobreescribir este método para evitar que intente acceder a un modelo
        return null;
    }

    public static function getPages(): array
    {
        return [
            //'index' => Pages\ListReportes::route('/'),
            //'create' => Pages\CreateReporte::route('/create'),
            //'edit' => Pages\EditReporte::route('/{record}/edit'),
            'index' => Pages\ListReportes::route('/'),
        ];
    }

    public static function getActions(): array
    {
        return [
            Action::make('ordenPagoReporte')
                ->label('Orden de Pago')
                ->modalContent(fn () => view('modals.orden-pago-reporte', ['liquidacionId' => 1]))
                ->modalWidth('7xl'),
            // Aca agregar otros reportes
        ];
    }
}
