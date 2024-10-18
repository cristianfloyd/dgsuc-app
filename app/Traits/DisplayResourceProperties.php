<?php

namespace App\Traits;

use App\Filament\Actions\ViewResourcePropertiesAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\View;

trait DisplayResourceProperties
{
    /**
     * Get the current values of the properties.
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
     * Get the properties to be displayed.
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
                View::make('filament.components.properties-display')
                    ->data([
                        'properties' => $this->getPropertiesToDisplay(),
                    ]),
            ])
            ->columnSpan(2);
    }
}
