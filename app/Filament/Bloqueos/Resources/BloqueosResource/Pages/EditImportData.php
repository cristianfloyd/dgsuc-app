<?php

namespace App\Filament\Bloqueos\Resources\BloqueosResource\Pages;

use App\Models\Dh01;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\DatePicker;
use App\Models\Reportes\BloqueosDataModel;
use Filament\Forms\Components\Placeholder;
use App\Services\Mapuche\VerificacionMapucheService;
use App\Filament\Bloqueos\Resources\BloqueosResource;

class EditImportData extends EditRecord
{
    protected static string $resource = BloqueosResource::class;


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Estado del Registro')
                    ->schema([
                        Placeholder::make('estado')
                            ->content(fn($record) => $record->estado->getLabel()),

                        Placeholder::make('fecha_registro')
                            ->label('Registrado el')
                            ->content(fn($record) => $record->created_at->format('d/m/Y H:i')),
                    ])
                    ->columnSpan(1),
                Section::make('Datos principales del bloqueo')
                    ->description('')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nro_legaj')
                                ->label('Legajo')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $legajo = Dh01::find($state);
                                        if ($legajo) {
                                            $set('nombre_legajo', $legajo->nombre_completo);
                                        }
                                    }
                                }),
                            TextInput::make('nombre_legajo')
                                ->label('Nombre del Agente')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('nro_cargo')
                                ->label('Cargo')
                                ->required()
                                ->numeric()
                                ->minValue(1),
                        ]),
                    ])
                    ->columnSpan(1),
                Section::make('')
                    ->schema([
                        Placeholder::make('')
                            ->content(fn($record) => view('filament.components.cargo-info', [
                                'cargo' => $record->cargo
                            ])),
                    ])
                    ->visible(fn($record) => $record->cargo()->exists())
                    ->columnSpan(1),
                Grid::make(3)
                    ->schema([
                        Section::make('')
                            ->schema([
                                DatePicker::make('fecha_baja')
                                    ->label('Fecha de Baja')
                                    ->required(fn(Get $get): bool => $get('tipo') !== 'Licencia')
                                    ->beforeOrEqual('today'),

                                Select::make('tipo')
                                    ->label('Tipo de Bloqueo')
                                    ->options([
                                        'licencia' => 'Licencia',
                                        'fallecido' => 'Fallecido',
                                        'renuncia' => 'Renuncia',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        if ($get('tipo') === 'fallecido') {
                                            $set('chkstopliq', true);
                                        }
                                    }),

                                Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->rows(3)
                                    ->maxLength(255)
                                // ->columnSpanFull(),
                            ])->columns(2)
                            ->columnSpan(2),

                    ])->columns(3),

            ])
            ->columns(3);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('revalidar')
                ->label('Revalidar Registro')
                ->icon('heroicon-o-arrow-path')
                ->action(fn() => $this->record->validarEstado())
                ->visible(fn() => $this->record->estado === 'error_validacion'),
            Action::make('marcar_procesado')
                ->label('Marcar como Procesado')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->action(fn() => $this->record->marcarProcesado())
                ->visible(fn() => !$this->record->chkstopliq),
            Action::make('verificar_mapuche')
                ->label('Verificar en Mapuche')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Verificar datos en Mapuche')
                ->modalDescription('¿Desea verificar el legajo y cargo en el sistema Mapuche?')
                ->action(function () {
                    $service = app(VerificacionMapucheService::class);

                    $resultado = $service->verificarLegajoCargo(
                        $this->record->nro_legaj,
                        $this->record->nro_cargo
                    );

                    if (!$resultado['existe']) {
                        Notification::make()
                            ->danger()
                            ->title('Error de verificación')
                            ->body($resultado['mensaje'])
                            ->send();

                        throw new Halt();
                    }

                    $datos = $resultado['datos'];

                    Notification::make()
                        ->success()
                        ->title('Verificación exitosa')
                        ->body("Legajo: {$datos['legajo']} - Cargo: {$datos['cargo']}
                               Nombre: {$datos['nombre']}
                               Estado: {$datos['estado']}")
                        ->send();
                }),

            Action::make('historial')
                ->label('Ver Historial')
                ->icon('heroicon-o-clock')
                ->url(fn($record) => route('bloqueos.historial.index', $record)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Bloqueo actualizado')
            ->success()
            ->send();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['usuario_modificacion'] = auth()->guard('web')->user()->name;
        $data['fecha_modificacion'] = now();

        return $data;
    }

    protected function getFooterActions(): array
    {
        return [
            Action::make('ver_siguiente')
                ->label('Siguiente Registro con Error')
                ->icon('heroicon-o-arrow-right')
                ->action(function () {
                    $siguiente = BloqueosDataModel::where('id', '>', $this->record->id)
                        ->where('estado', 'error')
                        ->first();

                    if ($siguiente) {
                        $this->redirect(self::getUrl(['edit', 'record' => $siguiente]));
                    }
                }),
        ];
    }
}
