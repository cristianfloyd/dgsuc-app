<?php

namespace App\Filament\Pages;

use App\Models\Dh11;
use App\Models\Dh89;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Filament\Widgets\CargoCountWidget;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Database\Eloquent\Factories\Relationship;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;
    protected ?string $heading = '';


    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Section::make('')->schema([
                DatePicker::make('startDate')->label('Inicio'),
                DatePicker::make('endDate')->label('Fin'),
                Select::make('codigoescalafon')->label('Escalafon')
                    ->options(function () {
                        return collect(['TODOS' => 'Todos'])
                        ->merge(Dh89::join('dh11', 'dh89.codigoescalafon', '=', 'dh11.codigoescalafon')
                            ->select('dh89.descesc', 'dh11.codigoescalafon')
                            ->distinct()
                            ->pluck('dh89.descesc', 'dh11.codigoescalafon')
                            ->merge([
                                'DOCS' => 'Docente Secundario',
                                'DOCU' => 'Docente Universitario',
                            ])
                        );
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $codc_categs = Dh11::where('codigoescalafon', $state)
                            ->pluck('codc_categ')
                            ->toArray();
                        session(['selected_codc_categs' => $codc_categs]);
                    }),
            ])
                ->columns(3),
        ]);
    }
}
