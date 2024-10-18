<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Resources\Resource;

class DisplayPropertiesWidget extends Widget
{
    protected static string $view = 'filament.widgets.display-properties-widget';


    protected Resource $resourceClass;

    public function __construct(Resource $resourceClass)
    {
        $this->resourceClass = $resourceClass;
        parent::__construct();
    }


    public static function make(array $properties = []): \Filament\Widgets\WidgetConfiguration
    {
        $instance = app(static::class, $properties);

        return $instance->getConfiguration();
    }

    public function setResource(Resource $resourceClass): static
    {
        $this->resourceClass = $resourceClass;
        return $this;
    }

    public function getViewData(): array
    {
        return [
            'properties' => $this->resourceClass::getPropertyValues(),
        ];
    }
}
