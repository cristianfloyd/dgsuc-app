<?php

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\PuestoDesempenado;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Services\AfipMapucheExportService;
use App\Models\AfipMapucheMiSimplificacion;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource\Pages;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource\RelationManagers;

class AfipMapucheMiSimplificacionResource extends Resource
{
    protected static ?string $model = AfipMapucheMiSimplificacion::class;
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'Mi Simplificación';
    protected static ?string $pluralNavigationLabel = 'Mi Simplificación';
    protected static ?string $label = 'Mi Simplificación';
    protected static ?string $pluralLabel = 'Mi Simplificación';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-circle';
    protected static ?int $navigationSort = 3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nro_legaj')
                    ->required()
                    ->numeric(),
                TextInput::make('nro_liqui')
                    ->required()
                    ->maxLength(6),
                TextInput::make('sino_cerra')
                    ->required()
                    ->maxLength(1),
                TextInput::make('desc_estado_liquidacion')
                    ->required()
                    ->maxLength(50),
                TextInput::make('nro_cargo')
                    ->required()
                    ->numeric(),
                TextInput::make('periodo_fiscal')
                    ->required()
                    ->maxLength(6),
                TextInput::make('tipo_registro')
                    ->required()
                    ->maxLength(2)
                    ->default(01),
                TextInput::make('codigo_movimiento')
                    ->required()
                    ->maxLength(2)
                    ->default('AT'),
                TextInput::make('cuil')
                    ->required()
                    ->maxLength(11),
                TextInput::make('trabajador_agropecuario')
                    ->required()
                    ->maxLength(1)
                    ->default('N'),
                TextInput::make('modalidad_contrato')
                    ->maxLength(3)
                    ->default('008'),
                TextInput::make('inicio_rel_laboral')
                    ->required()
                    ->maxLength(10),
                TextInput::make('fin_rel_laboral')
                    ->maxLength(10),
                TextInput::make('obra_social')
                    ->maxLength(6)
                    ->default('000000'),
                TextInput::make('codigo_situacion_baja')
                    ->maxLength(2),
                TextInput::make('fecha_tel_renuncia')
                    ->maxLength(10),
                TextInput::make('retribucion_pactada')
                    ->maxLength(15),
                TextInput::make('modalidad_liquidacion')
                    ->required()
                    ->maxLength(1)
                    ->default(1),
                TextInput::make('domicilio')
                    ->maxLength(5),
                TextInput::make('actividad')
                    ->maxLength(6),
                Select::make('puesto')
                    ->options(PuestoDesempenado::class)
                    ->nullable()
                    ->columnSpanFull(),
                TextInput::make('rectificacion')
                    ->maxLength(2),
                TextInput::make('ccct')
                    ->maxLength(10),
                TextInput::make('tipo_servicio')
                    ->maxLength(3),
                TextInput::make('categoria')
                    ->maxLength(6),
                TextInput::make('fecha_susp_serv_temp')
                    ->maxLength(10),
                TextInput::make('nro_form_agro')
                    ->maxLength(10),
                TextInput::make('covid')
                    ->maxLength(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('periodo_fiscal')
                    ->label('Periodo Fiscal')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cuil')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('inicio_rel_laboral')
                    ->label('Inicio Rel. Laboral')
                    ->sortable(),
                TextColumn::make('fin_rel_laboral')
                    ->label('Fin Rel. Laboral')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('domicilio'),
                TextColumn::make('actividad'),
                TextColumn::make('puesto')
                    ->badge()
                    ->label('Puesto')
                    ->colors([
                        'primary' => fn($state) => $state === PuestoDesempenado::PROFESOR_UNIVERSITARIO->descripcion(),
                        'secondary' => fn($state) => $state === PuestoDesempenado::PROFESOR_SECUNDARIO->descripcion(),
                        'warning' => fn($state) => $state === PuestoDesempenado::DIRECTIVO->descripcion(),
                        'success' => fn($state) => $state === PuestoDesempenado::NODOCENTE->descripcion(),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sino_cerra')
                    ->options([
                        'S' => 'Cerrado',
                        'N' => 'Abierto',
                    ]),
                Tables\Filters\Filter::make('periodo_fiscal')
                    ->form([
                        TextInput::make('periodo_fiscal')
                            ->mask('999999')
                            ->label('Periodo Fiscal'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportTxt')
                    ->label('Exportar TXT')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        try {
                            return app(AfipMapucheExportService::class)->exportToTxt();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al exportar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->color('success')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('periodo_fiscal', 'desc');
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
            'index' => Pages\ListAfipMapucheMiSimplificacions::route('/'),
            'create' => Pages\CreateAfipMapucheMiSimplificacion::route('/create'),
            'edit' => Pages\EditAfipMapucheMiSimplificacion::route('/{record}/edit'),
        ];
    }

    // Agregar este método para manejar la consulta
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('*')
            ->addSelect(DB::raw('puesto as puesto_raw')); // Agregamos el campo puesto sin cast
    }
}
