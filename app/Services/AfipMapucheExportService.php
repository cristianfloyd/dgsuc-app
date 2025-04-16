<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Contracts\ExportServiceInterface;
use App\Models\AfipMapucheMiSimplificacion;

class AfipMapucheExportService implements ExportServiceInterface
{
    private ColumnMetadata $columnMetadata;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->columnMetadata = new ColumnMetadata();
        $this->columnMetadata->setSystem('miSimplificacion');
    }

    public function exportToTxt()
    {
        try {
            if (!AfipMapucheMiSimplificacion::exists()) {
                throw new \Exception('No hay registros para exportar');
            }

            $fileName = 'mi_simplificacion_' . now()->format('Ymd_His') . '.txt';
            $filePath = storage_path("app/{$fileName}");
            $handle = fopen($filePath, 'w');

            AfipMapucheMiSimplificacion::query()
                ->select('ami.*')
                ->from('suc.afip_mapuche_mi_simplificacion as ami')
                ->orderBy('ami.id')
                ->chunk(1000, function ($records) use ($handle) {
                    foreach ($records as $record) {
                        $line = $this->formatLine($record, $this->getFieldOrder(), $this->columnMetadata->getWidths());
                        fwrite($handle, $line . "\n");
                    }
                });

            fclose($handle);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error en exportación TXT: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getFieldOrder(): array
    {
        return [
            'tipo_registro',
            'codigo_movimiento',
            'cuil',
            'trabajador_agropecuario',
            'modalidad_contrato',
            'inicio_rel_laboral',
            'fin_rel_lab',
            'obra_social',
            'codigo_situacion_baja',
            'fecha_tel_renuncia',
            'retribucion_pactada',
            'modalidad_liquidacion',
            'domicilio',
            'actividad',
            'puesto',
            'rectificacion',
            'ccct',
            'categoria',
            'tipo_servicio',
            'fecha_susp_serv_temp',
            'nro_form_agro',
            'covid',
        ];
    }

    private function formatLine($record, array $fieldOrder, array $columnWidths): string
    {
        $line = "";
        foreach ($fieldOrder as $index => $field) {
            $width = $columnWidths[$index];
            $value = $this->formatField($record, $field, $width);
            $line .= $value;
        }

        return $line;
    }

    private function formatField($record, string $field, int $width): string
    {
        $value = $record->{$field} ?? '';

        // Convertir Enum a string si es necesario
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        // Formateo de números: remover comas y decimales
        if (in_array($field, ['retribucion_pactada'])) {
            // Convertir a float, multiplicar por 100 para preservar 2 decimales y convertir a entero
            $value = (int)(floatval(str_replace(',', '', $value)) * 100);
            return str_pad($value, $width, '0', STR_PAD_LEFT);
        }

        // Formateo específico por campo
        // Formateo específico por campo
        if (in_array($field, ['inicio_rel_lab', 'fin_rel_lab', 'fecha_tel_renuncia'])) {
            // Si el valor es nulo, 0, '0' o vacío, retornamos espacios
            if (empty($value) || $value === '0000000000' || $value === 0) {
                return str_repeat(' ', $width);
            }
            return date('Y/m/d', strtotime($value));
        }

        // Valores fijos
        if ($field === 'nro_form_agro') {
            $value = '          ';
        }
        if ($field === 'categoria') {
            $value = '999999';
        }

        if ($field === 'ccct') {
            $value = '9999/99';
        }

        if ($field === 'covid') {
            $value = ' ';
        }

        // Formateo de domicilio (5 dígitos con ceros a la izquierda)
        if ($field === 'domicilio') {
            $value = str_pad($value, 5, '0', STR_PAD_LEFT);
        }

        // Formateo de actividad (6 dígitos)
        if ($field === 'actividad') {
            $value = str_pad($value, 6, '0', STR_PAD_LEFT);
        }

        // Formateo para campos numéricos
        if (in_array($field, ['cuil'])) {
            $value = preg_replace('/[^0-9]/', '', $value); // Remover no-números
            return str_pad($value, $width, '0', STR_PAD_LEFT);
        }

        // Formateo por defecto para campos de texto
        $value = substr($value, 0, $width); // Asegurar que no exceda el ancho
        return str_pad($value, $width, ' ', STR_PAD_RIGHT);
    }
}
