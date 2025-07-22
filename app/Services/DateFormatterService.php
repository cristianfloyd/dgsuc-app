<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para manejar el formateo de fechas de manera segura
 * Proporciona métodos para convertir strings a objetos Carbon
 * con manejo de excepciones para valores no válidos.
 */
class DateFormatterService
{
    /**
     * Convierte un string a un objeto Carbon si es posible
     * Si no es una fecha válida, devuelve null o un valor predeterminado.
     *
     * @param mixed $dateString El string de fecha a convertir
     * @param string|null $format Formato de fecha opcional
     * @param mixed $default Valor predeterminado si la conversión falla
     *
     * @return Carbon|mixed Objeto Carbon o el valor predeterminado
     */
    public static function parseOrDefault($dateString, ?string $format = null, $default = null)
    {
        // Si el valor es nulo o "Sin definir", devolver el valor predeterminado
        if ($dateString === null || $dateString === 'Sin definir' || $dateString === '') {
            return $default;
        }

        try {
            // Intentar convertir a Carbon usando el formato especificado o auto-detección
            return $format ? Carbon::createFromFormat($format, $dateString) : Carbon::parse($dateString);
        } catch (InvalidFormatException $e) {
            // Registrar el error para depuración
            Log::debug("Error al parsear fecha: {$dateString}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Devolver el valor predeterminado
            return $default;
        }
    }

    /**
     * Formatea una fecha para mostrar, manejando valores no válidos.
     *
     * @param mixed $dateString El string de fecha a formatear
     * @param string $outputFormat Formato de salida deseado
     * @param string $defaultText Texto a mostrar si la fecha no es válida
     *
     * @return string Fecha formateada o texto predeterminado
     */
    public static function formatOrDefault($dateString, string $outputFormat = 'd/m/Y', string $defaultText = 'No disponible')
    {
        $date = self::parseOrDefault($dateString, null, null);

        return $date instanceof Carbon ? $date->format($outputFormat) : $defaultText;
    }
}
