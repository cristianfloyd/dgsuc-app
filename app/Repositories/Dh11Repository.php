<?php
namespace App\Repositories;

use App\Models\Dh11;
use App\Services\Mapuche\PeriodoFiscalService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class Dh11Repository implements Dh11RepositoryInterface
{
    private $periodoFiscalService;

    public function __construct(PeriodoFiscalService $periodoFiscalService)
    {
        $this->periodoFiscalService = $periodoFiscalService;
    }


    /**
     * Actualiza el campo impp_basic de un registro Dh11 con un nuevo valor, y actualiza los campos vig_caano y vig_cames con los valores proporcionados.
     *
     * @param \App\Models\Dh11 $category El registro Dh11 a actualizar.
     * @param float $percentage El porcentaje de incremento a aplicar al campo impp_basic.
     * @param array|null $periodoFiscal Un array opcional con los valores de año y mes para actualizar los campos vig_caano y vig_cames.
     * @return bool Verdadero si se guardaron los cambios correctamente, falso en caso contrario.
     */
    public function updateImppBasic(Dh11 $category, float $percentage, array $periodoFiscal = null): bool
    {
        // Calcular el factor de incremento con 4 decimales de precisión
        $factor = $percentage / 100 + 1;
        Log::debug("Factor de incremento: $factor");

        // Actualizar el campo impp_basic
        $category->impp_basic = round($category->impp_basic * $factor, 2);
        Log::debug("Nuevo valor de impp_basic: {$category->impp_basic}");

        // Actualizar el vig_caano y vig_cames
        $category->vig_caano = $periodoFiscal['year'];
        $category->vig_cames = $periodoFiscal['month'];

        // Guardar los cambios en la base de datos
        return $category->save();
        // return true;
    }

    /**
     * Actualiza el campo impp_basic de un registro Dh11 con un nuevo valor, y actualiza los campos vig_caano y vig_cames con los valores proporcionados.
     *
     * @param \App\Models\Dh11 $category El registro Dh11 a actualizar.
     * @param array $newImppBasic Un array con el nuevo valor de impp_basic.
     * @param array|null $periodoFiscal Un array opcional con los valores de año y mes para actualizar los campos vig_caano y vig_cames.
     * @return bool Verdadero si se guardaron los cambios correctamente, falso en caso contrario.
     */
    public function updateImppBasicWithHistoryNew(Dh11 $category, array $newImppBasic, array $periodoFiscal = null): bool
    {
        // Actualizar el campo impp_basic
        $category->impp_basic = $newImppBasic['impp_basic'];

        // Actualizar el vig_caano y vig_cames
        $category->vig_caano = $periodoFiscal['year'];
        $category->vig_cames = $periodoFiscal['month'];

        // Guardar los cambios en la base de datos
        return $category->save();
    }

    /**
     * Actualiza o crea un registro Dh11 en la base de datos.
     *
     * @param array $attributes Atributos para buscar o crear el registro.
     * @param array $values Valores para actualizar el registro.
     * @return \App\Models\Dh11 El registro Dh11 actualizado o creado.
     */
    public function updateOrCreate(array $attributes, array $values = []): Dh11
    {
        return Dh11::updateOrCreate($attributes, $values);
    }

    /**
     * Obtiene todos los registros actuales con un valor de impp_basic mayor a 0.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCurrentRecords(): Collection
    {
        return Dh11::all()->where('impp_basic', '>', 0);
    }
}
