<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use App\Filament\Resources\EmbargoResource;
use App\Models\EmbargoProcesoResult;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class ConfigureEmbargoParameters extends Page implements HasForms
{
    protected static string $resource = EmbargoResource::class;
    protected static string $view = 'filament.resources.embargo-resource.pages.configure-embargo-parameters';

    public $nroLiquiProxima;
    public $nroComplementarias;
    public $nroLiquiDefinitiva;
    public $insertIntoDh25;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nroLiquiProxima')
                    ->required()
                    ->numeric(),
                Select::make('nroComplementarias')
                    ->multiple()
                    ->options(/* opciones de complementarias */),
                TextInput::make('nroLiquiDefinitiva')
                    ->required()
                    ->numeric(),
                Toggle::make('insertIntoDh25')
                    ->label('Insertar en DH25'),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        // Lógica para guardar los parámetros y actualizar los datos
        EmbargoProcesoResult::updateData(
            $this->nroComplementarias,
            $this->nroLiquiDefinitiva,
            $this->nroLiquiProxima,
            $this->insertIntoDh25
        );

        // Redirigir a la página de listado
        return redirect()->to(EmbargoResource::getUrl());
    }
}
