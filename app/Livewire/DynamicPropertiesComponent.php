<?php

namespace App\Livewire;

use Livewire\Component;

class DynamicPropertiesComponent extends Component
{
    public $properties = [];

    public function mount(array $initialProperties)
    {
        $this->properties = $initialProperties;
    }

    public function updateProperty($key, $value)
    {
        if (array_key_exists($key, $this->properties)) {
            $this->properties[$key] = $value;
        }
    }

    public function render()
    {
        
        return view('livewire.dynamic-properties-component', [
            'properties' => $this->properties,
        ]);
    }
}
