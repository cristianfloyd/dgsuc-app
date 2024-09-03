<?php

namespace App\Filament\Resources\AfipMapucheSicossResource\Pages;

use Filament\Actions;
use App\Models\AfipMapucheSicoss;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\AfipMapucheSicossResource;

class EditAfipMapucheSicoss extends EditRecord
{
    protected static string $resource = AfipMapucheSicossResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getRecord(): AfipMapucheSicoss
    {
        $key = $this->record->getKey();

        if (!$key) {
            throw new \Exception('No se proporcionó un identificador de registro válido.');
        }

        $model = $this->getModel();

        return $model::query()
            ->where('periodo_fiscal', substr($key, 0, 6))
            ->where('cuil', substr($key, 7))
            ->firstOrFail();
    }

    protected function resolveRecord(int|string $key): AfipMapucheSicoss
    {
        $model = static::getModel();
        $instance = new $model();
        return $instance->resolveRouteBinding($key);
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
{
    // Validar los datos del formulario
    $data = $this->form->getState();

    // Obtener el modelo actual
    $record = $this->getRecord();

    // Actualizar el modelo con los nuevos datos
    $record->fill($data);

    // Guardar el modelo
    $record->save();

    // Ejecutar acciones adicionales si es necesario
    $this->afterSave();

    // Enviar notificación si se requiere
    if ($shouldSendSavedNotification) {
        Notification::make()
            ->title('Registro actualizado')
            ->success()
            ->send();
    }

    // Redirigir si es necesario
    if ($shouldRedirect) {
        $this->redirect($this->getResource()::getUrl('index'));
    }
}
}
