<?php
namespace App\Repositories;

use App\Models\Dh11;
use App\Models\Dh61;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Mapuche\PeriodoFiscalService;

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
     * @param Dh11 $category El registro Dh11 a actualizar.
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
     * @param Dh11 $category El registro Dh11 a actualizar.
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
     * @return \App\Models\Mapuche\Dh11 El registro Dh11 actualizado o creado.
     */
    public function updateOrCreate(array $attributes, array $values = []): Dh11
    {
        try {
            // Intenta actualizar o crear el registro en la tabla Dh11
            return Dh11::updateOrCreate($attributes, $values);
        } catch (QueryException $e) {
            // Manejo de excepción en caso de violación de unicidad
            if ($e->getCode() == '23505') {
                // Si el registro ya existe, intenta actualizarlo
                $dh11 = Dh11::where($attributes)->first();
                if ($dh11) {
                    $dh11->update($values);
                    return $dh11;
                }
            }
            // Lanza la excepción si no es una violación de unicidad
            throw $e;
        }
    }

    /**
     * Actualiza un registro Dh11 en la base de datos.
     *
     * @param array $attributes Atributos para identificar el registro.
     * @param DH61 $values Valores a actualizar.
     * @return bool
     */
    public function update(array $attributes, Dh61 $values): bool
    {
        $dh11 = Dh11::where('codc_categ','=', $attributes['codc_categ'])->first();

        if ($dh11) {
            return $dh11->update($values->toArray());
        }
        return false;
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

    /**
     * Obtiene una lista de todas las codc_categ.
     *
     * @return array
     */
    public function getAllCodcCateg(): array
    {
        return Dh11::pluck('codc_categ')->toArray();
    }
}
