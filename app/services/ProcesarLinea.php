<?php
// App\Services\ProcesarLinea.php
namespace App\Services;

use App\Contracts\ProcesarLineaContract;

class ProcesarLinea implements ProcesarLineaContract
{
    public function procesar(string $line, array $columnWidths): array
    {
        // Aquí iría la lógica para procesar la línea
        $datosProcesados = [];
        $currentPosition = 0;
        
        foreach ($columnWidths as $index => $columnWidth) {
            $startPosition = $currentPosition;
            $endPosition = $currentPosition + $columnWidth;
            $columnValue = substr($line, $startPosition, $columnWidth);
            $datosProcesados[$index] = $columnValue;
            $currentPosition += $endPosition;
        }
        return $datosProcesados;
    }
}
