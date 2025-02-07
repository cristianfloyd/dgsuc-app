<?php

namespace App\Traits;

use App\Enums\PuestoDesempenado;

trait HasPuestoDesempenado
{
    use CategoriasConstantTrait;

    /**
     * Determina el puesto desempeñado basado en la categoría
     */
    public function determinarPuestoDesempenado(?string $categoria): ?PuestoDesempenado
    {
        if (!$categoria) return null;

        // Obtener el grupo de la categoría usando el método del trait
        $grupo = $this->getGroupByCategory($categoria);

        return match ($grupo) {
            'DOCU' => PuestoDesempenado::PROFESOR_UNIVERSITARIO,
            'DOCS', 'DOC2' => PuestoDesempenado::PROFESOR_SECUNDARIO,
            'NODO' => PuestoDesempenado::NODOCENTE,
            'AUTU', 'AUTS' => PuestoDesempenado::DIRECTIVO,
            default => null,
        };
    }

    /**
     * Obtiene la descripción del puesto basada en la categoría
     */
    public function getPuestoDescripcionFromCategoria(?string $categoria): ?string
    {
        return $this->determinarPuestoDesempenado($categoria)?->descripcion();
    }

    /**
     * Obtiene el escalafón basado en la categoría
     */
    public function getPuestoEscalafonFromCategoria(?string $categoria): ?string
    {
        return $this->determinarPuestoDesempenado($categoria)?->escalafon();
    }
}
