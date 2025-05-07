<?php

namespace App\Filament\Liquidaciones\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\LiquidacionControl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Liquidaciones\Resources\LiquidacionControlResource\Pages;
use App\Filament\Liquidaciones\Resources\LiquidacionControlResource\RelationManagers;

class LiquidacionControlResource extends Resource
{
    protected static ?string $model = LiquidacionControl::class;

    protected static ?string $navigationGroup = 'Liquidaciones';
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $modelLabel = 'Control Post-Liquidación';
    protected static ?string $pluralModelLabel = 'Controles Post-Liquidación';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_control')
                    ->required()
                    ->label('Nombre del Control'),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción'),
                Forms\Components\Select::make('nro_liqui')
                    ->label('Liquidación')
                    ->required()
                    ->relationship('liquidacion', 'desc_liqui'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'completado' => 'Completado',
                        'error' => 'Error',
                    ])
                    ->default('pendiente')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_control')
                    ->label('Control')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nro_liqui')
                    ->label('Liquidación')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'error' => 'danger',
                        'completado' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('resultado')
                    ->label('Resultado')
                    ->limit(50),
                Tables\Columns\TextColumn::make('fecha_ejecucion')
                    ->label('Ejecutado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('ejecutar')
                    ->action(fn ($record) => static::ejecutarControl($record))
                    ->icon('heroicon-o-play'),
                Tables\Actions\Action::make('ver_detalles')
                    ->action(fn ($record) => static::verDetallesControl($record))
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalles del Control')
                    ->modalContent(fn ($record) => view('filament.liquidaciones.detalles-control', ['control' => $record])),
                Tables\Actions\EditAction::make(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'completado' => 'Completado',
                        'error' => 'Error',
                    ]),
                Tables\Filters\Filter::make('nro_liqui')
                    ->form([
                        Forms\Components\TextInput::make('nro_liqui')
                            ->label('Número de Liquidación')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['nro_liqui'],
                            fn (Builder $query, $nroLiqui): Builder => $query->where('nro_liqui', $nroLiqui)
                        );
                    }),
            ]);
    }

    // Implementa el método para ejecutar controles
    public static function ejecutarControl($record)
    {
        // Aquí iría la lógica para ejecutar el control específico basado en $record->nombre_control
        // usando LiquidacionControlService

        // Ejemplo:
        $service = new \App\Services\LiquidacionControlService();
        $result = match($record->nombre_control) {
            'controlar_negativos' => $service->controlarNegativos($record->nro_liqui),
            'controlar_cargos_liquidados' => $service->controlarCargosLiquidados($record->nro_liqui),
            // Agrega más casos según los controles que tengas
            default => null,
        };

        if ($result) {
            $record->update([
                'estado' => $result->success ? 'completado' : 'error',
                'resultado' => $result->message,
                'datos_resultado' => $result->data,
                'fecha_ejecucion' => now(),
                'ejecutado_por' => auth()->guard('web')->user()->name,
            ]);
        }
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiquidacionControls::route('/'),
            'create' => Pages\CreateLiquidacionControl::route('/create'),
            'edit' => Pages\EditLiquidacionControl::route('/{record}/edit'),
        ];
    }
}
