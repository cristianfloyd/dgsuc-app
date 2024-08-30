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

    public function updateOrCreate(array $attributes, array $values = []): Dh11
    {
        return Dh11::updateOrCreate($attributes, $values);
    }

    public function getAllCurrentRecords(): Collection
    {
        return Dh11::all()->where('impp_basic', '>', 0);
    }
}
