<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControls\LiquidacionControls;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use App\Services\LiquidacionControlService;
use App\Filament\Liquidaciones\Resources\LiquidacionControls\Pages\ListLiquidacionControls;
use App\Filament\Liquidaciones\Resources\LiquidacionControls\Pages\CreateLiquidacionControl;
use App\Filament\Liquidaciones\Resources\LiquidacionControls\Pages\EditLiquidacionControl;
use App\Filament\Liquidaciones\Resources\LiquidacionControlResource\Pages;
use App\Models\LiquidacionControl;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LiquidacionControlResource extends Resource
{
    protected static ?string $model = LiquidacionControl::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Liquidaciones';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $modelLabel = 'Control Post-Liquidación';

    protected static ?string $pluralModelLabel = 'Controles Post-Liquidación';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre_control')
                    ->required()
                    ->label('Nombre del Control'),
                Textarea::make('descripcion')
                    ->label('Descripción'),
                Select::make('nro_liqui')
                    ->label('Liquidación')
                    ->required()
                    ->relationship('liquidacion', 'desc_liqui'),
                Select::make('estado')
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
                TextColumn::make('nombre_control')
                    ->label('Control')
                    ->searchable(),
                TextColumn::make('nro_liqui')
                    ->label('Liquidación')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'error' => 'danger',
                        'completado' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('resultado')
                    ->label('Resultado')
                    ->limit(50),
                TextColumn::make('fecha_ejecucion')
                    ->label('Ejecutado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('ejecutar')
                    ->action(fn ($record) => static::ejecutarControl($record))
                    ->icon('heroicon-o-play'),
                Action::make('ver_detalles')
                    ->action(fn ($record) => static::verDetallesControl($record))
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalles del Control')
                    ->modalContent(fn ($record) => view('filament.liquidaciones.detalles-control', ['control' => $record])),
                EditAction::make(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'completado' => 'Completado',
                        'error' => 'Error',
                    ]),
                Filter::make('nro_liqui')
                    ->schema([
                        TextInput::make('nro_liqui')
                            ->label('Número de Liquidación')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['nro_liqui'],
                            fn (Builder $query, $nroLiqui): Builder => $query->where('nro_liqui', $nroLiqui),
                        );
                    }),
            ]);
    }

    // Implementa el método para ejecutar controles
    public static function ejecutarControl($record): void
    {
        // Aquí iría la lógica para ejecutar el control específico basado en $record->nombre_control
        // usando LiquidacionControlService

        // Ejemplo:
        $service = new LiquidacionControlService();
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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLiquidacionControls::route('/'),
            'create' => CreateLiquidacionControl::route('/create'),
            'edit' => EditLiquidacionControl::route('/{record}/edit'),
        ];
    }
}
