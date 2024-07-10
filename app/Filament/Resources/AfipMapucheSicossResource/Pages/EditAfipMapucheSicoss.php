<?php

namespace App\Filament\Resources\AfipMapucheSicossResource\Pages;

use App\Filament\Resources\AfipMapucheSicossResource;
use App\Models\AfipMapucheSicoss;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheSicoss extends EditRecord
{
    protected static string $resource = AfipMapucheSicossResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['id'] = $data['periodo_fiscal'] . '|' . $data['cuil'];
        return $data;
    }

    public function resolveRecord($key) :AfipMapucheSicoss
    {
        $model = static::getResource()::getModel();

        [ $periodo_fiscal, $cuil ] = explode('|', $key);
        return $model::query()
            ->where('periodo_fiscal', $periodo_fiscal)
            ->where('cuil', $cuil)
            ->firstOrFail();
    }
}
