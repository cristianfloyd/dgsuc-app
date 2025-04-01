<?php

namespace App\DTOs\Sicoss;

/**
 * DTO para transferencia de datos de estadísticas de resumen
 * Proporciona una estructura tipada para los datos
 */
class ResumenStatsDTO
{
    /**
     * Totales generales
     */
    public array $totales;
    
    /**
     * Diferencias por dependencia
     */
    public array $diferencias_por_dependencia;
    
    /**
     * Totales monetarios
     */
    public array $totales_monetarios;
    
    /**
     * Comparación con 931
     */
    public array $comparacion_931;
    
    /**
     * CUILs no encontrados
     */
    public array $cuils_no_encontrados;
    
    /**
     * Crea un DTO a partir de un array
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->totales = $data['totales'] ?? [];
        $dto->diferencias_por_dependencia = $data['diferencias_por_dependencia'] ?? [];
        $dto->totales_monetarios = $data['totales_monetarios'] ?? [];
        $dto->comparacion_931 = $data['comparacion_931'] ?? [];
        $dto->cuils_no_encontrados = $data['cuils_no_encontrados'] ?? [];
        
        return $dto;
    }
    
    /**
     * Convierte el DTO a un array
     */
    public function toArray(): array
    {
        return [
            'totales' => $this->totales,
            'diferencias_por_dependencia' => $this->diferencias_por_dependencia,
            'totales_monetarios' => $this->totales_monetarios,
            'comparacion_931' => $this->comparacion_931,
            'cuils_no_encontrados' => $this->cuils_no_encontrados,
        ];
    }
}
