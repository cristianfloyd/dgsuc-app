<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class DynamicPropertiesComponent extends Component
{
    public $properties = [];
    protected $listeners = ['propertiesUpdated' => 'updateAllProperties'];


    public function mount(array $initialProperties)
    {
        $this->properties = $initialProperties;
        Log::debug('Initial properties:', $this->properties);
    }

    public function updateProperty($key, $value)
    {
        if (array_key_exists($key, $this->properties)) {
            $this->properties[$key] = $value;
        }
    }

    public function updateAllProperties($newProperties)
    {
        $this->properties = $newProperties;
    }


    public function render()
    {

        return view('livewire.dynamic-properties-component', [
            'properties' => $this->properties,
        ]);
    }
}
