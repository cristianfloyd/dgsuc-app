<?php
namespace App\Repositories;

use App\Models\Dh11;

class Dh11Repository implements Dh11RepositoryInterface
{
    public function updateImppBasic(Dh11 $category, float $percentage, array $periodoFiscal): bool
    {
        // Calcular el factor de incremento con 4 decimales de precisión
        $factor = round(1 + $percentage / 100, 4);

        // Actualizar el campo impp_basic
        $category->impp_basic = round($category->impp_basic * $factor, 2);

        // Actualizar el vig_caano y vig_cames
        $category->vig_caano = $periodoFiscal['year'];
        $category->vig_cames = $periodoFiscal['month'];

        // Guardar los cambios en la base de datos
        return $category->save();
    }
}
