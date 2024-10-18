<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Resources\Resource;

class DisplayPropertiesWidget extends Widget
{
    protected static string $view = 'filament.widgets.display-properties-widget';


    protected Resource $resourceClass;

    public function __construct(string $resourceClass)
    {
        if (!is_subclass_of($resourceClass, Resource::class)) {
            throw new \InvalidArgumentException("La clase debe ser una subclase de Filament\Resources\Resource.");
        }

        $this->resourceClass = app($resourceClass);
        
        parent::__construct();
    }

    public static function createWithResource(string $resourceClass)
    {
        if (!is_subclass_of($resourceClass, Resource::class)) {
            throw new \InvalidArgumentException("La clase debe ser una subclase de Filament\Resources\Resource.");
        }

        // $resourceInstance = app($resourceClass);
        return  $resourceClass;
    }

    public static function make(array $properties = []): \Filament\Widgets\WidgetConfiguration
    {
        // Validar que la clase de recurso esté presente y sea válida
        if (!isset($properties['resourceClass']) || !is_subclass_of($properties['resourceClass'], Resource::class)) {
            throw new \InvalidArgumentException("Debe proporcionar una clase de recurso válida que extienda Filament\Resources\Resource.");
        }

        try {

            // Crear una instancia de la clase con las propiedades proporcionadas
            $instance = new static($properties['resourceClass']);
            return $instance->getConfiguration();
        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra durante la instanciación
            throw new \RuntimeException("Error al crear la instancia de DisplayPropertiesWidget: " . $e->getMessage());
        }
    }



    /**
     * Establece el recurso Filament que se utilizará en este widget.
     *
     * @param string $resourceClass La clase de recurso Filament que se utilizará.
     * @return static Devuelve la instancia del widget para permitir el encadenamiento de métodos.
     * @throws \InvalidArgumentException Si la clase proporcionada no es una subclase de Filament\Resources\Resource.
     */
    public function setResource(string $resourceClass): static
    {
        if (!is_subclass_of($resourceClass, Resource::class)) {
            throw new \InvalidArgumentException("La clase debe ser una subclase de Filament\Resources\Resource.");
        }

        $this->resourceClass = app($resourceClass);
        return $this;
    }

    public function getViewData(): array
    {
        return [
            'properties' => $this->resourceClass::getPropertyValues(),
        ];
    }

    public function initialize(string $resourceClass): void
    {
        if (!is_subclass_of($resourceClass, Resource::class)) {
            throw new \InvalidArgumentException("The class must be a subclass of Filament\Resources\Resource.");
        }

        $this->resourceClass = app($resourceClass);
    }
}
