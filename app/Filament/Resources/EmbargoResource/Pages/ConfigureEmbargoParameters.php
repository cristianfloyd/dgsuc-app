<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use App\Filament\Resources\EmbargoResource;
use App\Models\EmbargoProcesoResult;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;


/**
 * @property mixed $form
 */
class ConfigureEmbargoParameters extends Page implements HasForms
{
    protected static string $resource = EmbargoResource::class;
    protected static string $view = 'filament.resources.embargo-resource.pages.configure-embargo-parameters';

    public int $nroLiquiProxima = 0;
    public ?array $nroComplementarias = [];
    public int $nroLiquiDefinitiva = 0;
    public bool $insertIntoDh25 = false;
    public mixed $data;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nroLiquiProxima')
                    ->required()
                    ->numeric(),
                Select::make('nroComplementarias')
                    ->multiple()
                    ->options([
                        2 => '2',
                        3 => '3'
                    ]),
                TextInput::make('nroLiquiDefinitiva')
                    ->required()
                    ->numeric(),
                Toggle::make('insertIntoDh25')
                    ->label('Insertar en DH25'),
            ])
            ->statePath('data');
    }

    public function submit(): \Illuminate\Http\RedirectResponse
    {
        $data = $this->form->getState();

        // Lógica para guardar los parámetros y actualizar los datos
        EmbargoProcesoResult::updateData(
            $data['nroComplementarias'] ?? [],
            $data['nroLiquiDefinitiva'] ?? 0,
            $data['nroLiquiProxima'] ?? 0,
            $data['insertIntoDh25'] ?? false
        );

        // Redirigir o mostrar un mensaje de éxito
        Notification::make()
            ->title('Configuration saved successfully')
            ->success()
            ->send();

        // Redirigir a la página de listado
        return redirect()->to(EmbargoResource::getUrl());
    }
}
