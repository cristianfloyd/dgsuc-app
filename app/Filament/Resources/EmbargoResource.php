<?php

namespace App\Filament\Resources;

use Livewire\Livewire;
use Filament\Tables\Table;
use AllowDynamicProperties;
use App\Tables\EmbargoTable;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Models\EmbargoProcesoResult;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Traits\DisplayResourceProperties;
use App\Filament\Resources\EmbargoResource\Pages;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;

#[AllowDynamicProperties] class EmbargoResource extends Resource
{
    use DisplayResourceProperties;

    protected static ?string $model = EmbargoProcesoResult::class;
    protected static ?string $modelLabel = 'Embargo';
    protected static ?string $slug = 'embargos';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected EmbargoTable $embargoTable;


    public static int $nroLiquiProxima = 0;
    public static array $nroComplementarias = [];
    public static int $nroLiquiDefinitiva = 1;
    public static bool $insertIntoDh25 = false;

    public static function boot(EmbargoTable $embargoTable): void
    {
        parent::boot();

        static::$embargoTable = $embargoTable;

        static::$nroLiquiProxima = 4; // Lógica para determinar este valor
        static::$nroComplementarias = []; // Lógica para determinar este array
        static::$nroLiquiDefinitiva = 0; // Lógica para determinar este valor
        static::$insertIntoDh25 = false; // Lógica para determinar este booleano

        // Usar estas variables para actualizar los datos
        EmbargoProcesoResult::updateData(
            static::$nroComplementarias,
            static::$nroLiquiDefinitiva,
            static::$nroLiquiProxima,
            static::$insertIntoDh25
        );
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
        // Obtener los parámetros necesarios
        $parametros = static::obtenerParametros();

        // Actualizar las variables estáticas
        static::$nroLiquiProxima = $data['nroLiquiProxima'] ?? $parametros['nroLiquiProxima'];
        static::$nroComplementarias = $data['nroComplementarias'] ?? $parametros['nroComplementarias'];
        static::$nroLiquiDefinitiva = $data['nroLiquiDefinitiva'] ?? $parametros['nroLiquiDefinitiva'];
        static::$insertIntoDh25 = $data['insertIntoDh25'] ?? $parametros['insertIntoDh25'];
        
        // Llamar a EmbargoProcesoResult::updateData con los parámetros actualizados
        EmbargoProcesoResult::updateData(
            static::$nroComplementarias,
            static::$nroLiquiDefinitiva,
            static::$nroLiquiProxima,
            static::$insertIntoDh25
        );

        // Opcional: Mostrar un mensaje de éxito
        Notification::make()
            ->title('Datos actualizados correctamente')
            ->success()
            ->send();
    }

    private static function obtenerParametros(): array
    {
        // Aquí deberías implementar la lógica para obtener los parámetros
        // Por ejemplo, podrías obtenerlos de la base de datos o de alguna configuración

        return [
            'nroLiquiProxima' => 5, // Ejemplo: incrementar en 1
            'nroComplementarias' => [1, 2, 3], // Ejemplo: array con valores
            'nroLiquiDefinitiva' => 1, // Ejemplo: incrementar en 1
            'insertIntoDh25' => true, // Ejemplo: alternar el valor
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmbargos::route('/'),
            'dashboard' => Pages\DashboardEmbargo::route('/admin'),
            'configure' => Pages\ConfigureEmbargoParameters::route('/configure'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return EmbargoProcesoResult::getEmptyQuery();
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


    public static function getPropertiesToDisplay(): array
    {
        return [
            'nroLiquiProxima' => static::$nroLiquiProxima,
            'nroComplementarias' => static::$nroComplementarias,
            'nroLiquiDefinitiva' => static::$nroLiquiDefinitiva,
            'insertIntoDh25' => static::$insertIntoDh25,
        ];
    }

    public static function getPropertiesForLivewire(): array
    {
        return [
            'nroLiquiProxima' => static::$nroLiquiProxima,
            'nroComplementarias' => static::$nroComplementarias,
            'nroLiquiDefinitiva' => static::$nroLiquiDefinitiva,
            'insertIntoDh25' => static::$insertIntoDh25,
        ];
    }

    /**
     * Actualiza las propiedades de un componente Livewire.
     *
     *
     *
     */
    public static function updateProperties(array $newValues)
    {



    }
}
