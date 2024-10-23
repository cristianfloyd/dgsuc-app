<?php

namespace App\Filament\Pages;

use App\Models\Dh11;
use App\Models\Dh89;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

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
                        $codc_categs = collect(['TODO' => 'Todos'])
                        ->merge(
                            /**
                             * La colección se construye combinando los códigos de escalafón distintos obtenidos de la tabla 'dh11' con las descripciones de escalafón de la tabla 'dh89', y luego se agrega la opción 'Todos' y las opciones para los diferentes tipos de escalafón.
                             *
                             * @return \Illuminate\Support\Collection Una colección de opciones de escalafón.
                             */
                            Dh89::join('dh11', 'dh89.codigoescalafon', '=', 'dh11.codigoescalafon')
                                ->select('dh89.descesc', 'dh11.codigoescalafon')
                                ->distinct()
                                ->pluck('dh89.descesc', 'dh11.codigoescalafon')
                                ->merge([
                                'DOCS' => 'Docente Secundario',
                                'DOCU' => 'Docente Universitario',
                                'AUTU' => 'Autoridad Universitario',
                                'AUTS' => 'Autoridad Secundario',
                            ])
                        );
                        return $codc_categs;
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        /**
                         * Almacena los códigos de categoría seleccionados en la sesión basados en el código de escalafón seleccionado.
                         *
                         * Esta función se ejecuta después de que se actualiza el estado del campo de selección de escalafón.
                         * Obtiene los códigos de categoría de la tabla 'dh11' que coinciden con el código de escalafón seleccionado,
                         * y los almacena en la sesión con la clave 'selected_codc_categs'.
                         *
                         * @param string $state El código de escalafón seleccionado.
                         * @param callable $set Función para establecer el estado del campo de selección.
                         */
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
