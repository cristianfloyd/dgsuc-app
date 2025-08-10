<?php

use App\Helpers\MoneyFormatter;

if (!\function_exists('money')) {
    function money($value)
    {
        return MoneyFormatter::format($value);
    }
}

if (!\function_exists('nombreMes')) {
    function nombreMes(int $mes): string
    {
        return match ($mes) {
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
            default => ''
        };
    }
}

if (!\function_exists('formatoImporte')) {
    function formatoImporte(float $importe, int $decimales = 2): string
    {
        return number_format($importe, $decimales, ',', '.');
    }
}

if (!\function_exists('formatoPeriodo')) {
    function formatoPeriodo(int $mes, int $anio): string
    {
        return nombreMes($mes) . ' ' . $anio;
    }
}

if (!\function_exists('formatoLiquidacion')) {
    function formatoLiquidacion(int $numero): string
    {
        return str_pad($numero, 4, '0', \STR_PAD_LEFT);
    }
}

if (!\function_exists('validarPeriodo')) {
    function validarPeriodo(int $mes, int $anio): bool
    {
        return $mes >= 1 && $mes <= 12 && $anio >= 2000;
    }
}

if (!\function_exists('limpiarTexto')) {
    function limpiarTexto(string $texto, ?int $longitud = null): string
    {
        $texto = trim($texto);
        return $longitud ? substr($texto, 0, $longitud) : $texto;
    }
}

if (!\function_exists('formatoArea')) {
    function formatoArea(string $area): string
    {
        return str_pad($area, 3, '0', \STR_PAD_LEFT);
    }
}

if (!\function_exists('esRetencion')) {
    function esRetencion(?string $tipo): bool
    {
        return \in_array($tipo, ['A', 'D']);
    }
}

if (!\function_exists('tipoComprobanteTexto')) {
    function tipoComprobanteTexto(string $tipo): string
    {
        return match ($tipo) {
            'A' => 'Aporte',
            'D' => 'Descuento',
            'N' => 'Neto',
            default => 'Otro'
        };
    }
}

if (!\function_exists('estadoComprobante')) {
    function estadoComprobante(bool $requiere_cheque): string
    {
        return $requiere_cheque ? 'Requiere Cheque' : 'Sin Cheque';
    }
}
