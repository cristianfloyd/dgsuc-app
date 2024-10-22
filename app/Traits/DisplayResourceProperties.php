<?php

namespace App\Traits;

use Livewire\Livewire;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Filament\Forms\Components\Section;
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

    public function resetPropertiesToDefault(): void
    {
            try {
                $defaultProperties = $this->getDefaultProperties();
                Cache::put($this->getCacheKey(), $defaultProperties, now()->addMinutes(30));

                Log::debug('Propiedades restablecidas a los valores por defecto.', [
                    'defaultProperties' => $defaultProperties,
                ]);
            } catch (\Exception $e) {
                Log::error('Error al restablecer las propiedades a los valores por defecto.', [
                    'error' => $e->getMessage(),
                ]);
            }

    }

    /**
     * Establece los valores de las propiedades y actualiza el caché.
     *
     * @param array $properties
     * @return void
     */
    public function setPropertyValues(array $properties): void
    {
        // Obtiene las propiedades actuales que se mostrarán.
        $currentProperties = $this->getPropertiesToDisplay();

        // Combina las propiedades actuales con las nuevas propiedades proporcionadas.
        $updatedProperties = array_merge($currentProperties, $properties);

        // Actualiza la caché con las propiedades actualizadas y establece un tiempo de expiración de 30 minutos.
        Cache::put($this->getCacheKey(), $updatedProperties, now()->addMinutes(30));

        // Despacha un evento indicando que las propiedades han sido actualizadas.
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
     * Agrega una nueva propiedad al array de propiedades.
     *
     * @param string $key La clave de la nueva propiedad
     * @param mixed $value El valor de la nueva propiedad
     * @return void
     */
    public function addProperty(string $key, mixed $value): void
    {
        $properties = $this->getPropertiesToDisplay();
        $properties[$key] = $value;
        $this->setPropertyValues($properties);
    }


    /**
     * Crea un componente de tarjeta para mostrar las propiedades.
     *
     * @return Section
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
