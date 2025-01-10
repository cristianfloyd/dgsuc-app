<?php

namespace App\Rules;

use Closure;
use App\Models\Dh03;
use Illuminate\Contracts\Validation\ValidationRule;

class LegajoCargoExistsRule implements ValidationRule
{
    private int $nroLegaj;
    private int $nroCargo;

    public function __construct(int $nroLegaj, int $nroCargo)
    {
        $this->nroLegaj = $nroLegaj;
        $this->nroCargo = $nroCargo;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Dh03::validarLegajoCargo($this->nroLegaj, $this->nroCargo)->exists();

        if (!$exists) {
            $fail("La combinación de legajo {$this->nroLegaj} y cargo {$this->nroCargo} no existe en el sistema.");
        }
    }
}
