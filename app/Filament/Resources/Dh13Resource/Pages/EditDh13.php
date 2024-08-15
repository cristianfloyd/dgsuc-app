<?php

namespace App\Filament\Resources\Dh13Resource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\Dh13Resource;
use Filament\Resources\Pages\EditRecord;

class EditDh13 extends EditRecord
{
    protected static string $resource = Dh13Resource::class;

    /**
     * Devuelve las acciones de encabezado para la página de edición de registros Dh13.
     *
     * Las acciones de encabezado son las opciones que se muestran en la parte superior de la página de edición, como la opción de eliminar el registro.
     *
     * @return array Las acciones de encabezado para la página de edición.
     */
    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    /**
     * Resuelve el registro Dh13 a partir de una clave compuesta.
     *
     * Divide la clave compuesta en sus partes individuales (codn_conce y nro_orden_formula),
     * y luego busca el registro correspondiente en la base de datos.
     *
     * @param int|string $key La clave compuesta del registro a resolver.
     * @return \Illuminate\Database\Eloquent\Model El registro Dh13 encontrado.
     */
    protected function resolveRecord(int|string $key): Model
    {
        $resource = static::getResource();
        $model = $resource::getModel();

        // Dividir la clave compuesta
        [$codn_conce, $nro_orden_formula] = explode('-', $key);

        // Buscar el registro usando ambas partes de la clave primaria
        $record = $model::query()
            ->where('codn_conce', $codn_conce)
            ->where('nro_orden_formula', $nro_orden_formula)
            ->first();

        if (!$record) {
            $this->redirect($resource::getUrl('index'));
        }

        return $record;
    }

    /**
     * Actualiza un registro existente en el modelo Dh13 con los datos proporcionados.
     *
     * @param \Illuminate\Database\Eloquent\Model $record El registro a actualizar.
     * @param array $data Los datos a actualizar en el registro.
     * @return \Illuminate\Database\Eloquent\Model El registro actualizado.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Asegurarse de que no se modifiquen las claves primarias
        unset($data['codn_conce'], $data['nro_orden_formula']);

        $record->update($data);

        return $record;
    }

    /**
     * Obtiene la URL de redireccionamiento después de actualizar un registro.
     *
     * @return string La URL de redireccionamiento.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
