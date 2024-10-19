<?php

namespace App\Traits;

use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View as ComponentsView;
use App\Filament\Actions\ViewResourcePropertiesAction;

trait DisplayResourceProperties
{
    /**
     * Obtener los valores actuales de las propiedades.
     *
     * @return array
     */
    public static function getPropertyValues(): array
    {
        $values = [];
        foreach (self::getPropertiesToDisplay() as $property) {
            $values[$property] = $property;
        }
        return $values;
    }

    /**
     * Obtener las propiedades que se mostrarán.
     *
     * @return array
     */
    abstract public static function getPropertiesToDisplay(): array;

    public static function getViewPropertiesAction(): ViewResourcePropertiesAction
    {
        return ViewResourcePropertiesAction::make();
    }

    /**
     * Agrega la tarjeta de propiedades al esquema del formulario del recurso.
     *
     * @param array $schema
     * @return array
     */
    public function addPropertiesToFormSchema(array $schema): array
    {
        return array_merge([$this->getPropertiesCard()], $schema);
    }

    /**
     * Crea un componente de tarjeta para mostrar las propiedades.
     *
     * @return \Filament\Forms\Components\Section
     */
    public function getPropertiesCard(): Section
    {
        return Section::make()
            ->schema([
                //
            ])
            ->columnSpan(2);
    }
}
