<?php

namespace App\Traits;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View as ComponentsView;
use App\Filament\Actions\ViewResourcePropertiesAction;


trait DisplayResourceProperties
{
    /**
     * Obtener las propiedades que se mostrarán.
     *
     * @return array
     */
    abstract protected function getDefaultProperties(): array;


    /**
     * Obtiene la clave de caché para las propiedades del recurso.
     *
     * @return string
     */
    protected function getCacheKey(): string
    {
        return 'properties.' . static::class;
    }

    /**
     * Obtiene las propiedades almacenadas en caché o las propiedades por defecto si no existen en caché.
     *
     * @return array
     */
    protected function getCachedProperties(): array
    {
        return Cache::remember($this->getCacheKey(), now()->addMinutes(30), function () {
            return $this->getDefaultProperties();
        });
    }

    /**
     * Obtiene las propiedades a mostrar.
     * Este método utiliza el caché para obtener las propiedades.
     *
     * @return array
     */
    public function getPropertiesToDisplay(): array
    {
        return Cache::remember($this->getCacheKey(), now()->addMinutes(30), function () {
            return $this->getDefaultProperties();
        });
    }


    /**
     * Establece los valores de las propiedades y actualiza el caché.
     *
     * @param array $properties
     * @return void
     */
    public function setPropertyValues(array $properties): void
    {
        $currentProperties = $this->getPropertiesToDisplay();
        $updatedProperties = array_merge($currentProperties, $properties);
        Cache::put($this->getCacheKey(), $updatedProperties, now()->addMinutes(30));
        Event::dispatch('propertiesUpdated', $updatedProperties);
    }

    /**
     * Actualiza los datos de las propiedades.
     *
     * @param array $data
     * @return void
     */
    public function actualizarDatos(array $data = []): void
    {
        $this->setPropertyValues($data);
    }




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
