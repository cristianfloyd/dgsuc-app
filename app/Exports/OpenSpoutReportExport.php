<?php

namespace App\Exports;

use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Properties;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function strlen;

class OpenSpoutReportExport
{
    protected $columns = [
        'nro_liqui' => 'Número',
        'desc_liqui' => 'Liquidación',
        'apellido' => 'Apellido',
        'nombre' => 'Nombre',
        'cuil' => 'DNI',
        'nro_legaj' => 'Legajo',
        'nro_cargo' => 'Secuencia',
        'codc_uacad' => 'Dependencia',
        'codn_conce' => 'Concepto',
        'impp_conce' => 'Importe',
    ];

    protected string $tempFile;

    protected $summaryData;

    public function __construct(protected \Illuminate\Database\Eloquent\Builder $query)
    {
        $this->tempFile = 'temp/export_' . uniqid() . '.xlsx';

        // Preparar los datos para la hoja de resumen
        $this->prepareSummaryData();
    }

    public function download(string $fileName): StreamedResponse
    {
        // Asegurar que el directorio temporal exista
        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        // Preparar el archivo Excel usando OpenSpout
        $this->buildExcelFile();

        // Crear una respuesta HTTP que fluye el archivo y lo elimina después
        return new StreamedResponse(function (): void {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = Storage::readStream($this->tempFile);
            stream_copy_to_stream($fileStream, $outputStream);
            fclose($fileStream);
            fclose($outputStream);

            // Eliminar archivo temporal
            Storage::delete($this->tempFile);
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected function buildExcelFile(): void
    {
        // Crear propiedades del documento personalizadas
        $customProperties = new Properties(
            title: 'Reporte de Conceptos por Listado',
            subject: 'Informe de Conceptos',
            creator: 'Informes App',
            lastModifiedBy: 'Informes App',
            keywords: 'conceptos, liquidación, reportes',
            description: 'Reporte generado a través de OpenSpout para manejo de archivos de gran volumen',
            category: 'Reportes',
        );

        // Crear opciones para el escritor
        $options = new Options();
        $options->setProperties($customProperties);

        // Crear el escritor de OpenSpout
        $writer = new Writer($options);

        // Abrir el archivo para escribir
        $writer->openToFile(Storage::path($this->tempFile));

        // === HOJA PRINCIPAL: DATOS DETALLADOS ===
        $writer->getCurrentSheet()->setName('Reporte de Conceptos');
        $this->writeDetailSheet($writer);

        // === HOJA DE RESUMEN ===
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Resumen');
        $this->writeSummarySheet($writer);

        // Cerrar el escritor para finalizar el archivo
        $writer->close();
    }

    protected function writeDetailSheet(Writer $writer): void
    {
        // Crear borde fino para todas las celdas
        $borderStyle = new Border(
            new BorderPart(Border::LEFT, Color::BLACK, Border::WIDTH_THIN),
            new BorderPart(Border::RIGHT, Color::BLACK, Border::WIDTH_THIN),
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN),
        );

        // Crear estilo para la cabecera
        $headerStyle = new Style();
        $headerStyle->setFontBold();
        $headerStyle->setFontColor(Color::WHITE);
        $headerStyle->setBackgroundColor('4472C4');
        $headerStyle->setBorder($borderStyle);
        $headerStyle->setCellAlignment(CellAlignment::CENTER);

        // Escribir cabeceras
        $headerRow = Row::fromValues(array_values($this->columns), $headerStyle);
        $writer->addRow($headerRow);

        // Procesar los datos en chunks para optimizar memoria
        $rowIndex = 2; // Las filas se inician en 1, y la primera es el encabezado
        $this->query->cursor()->each(function ($record) use ($writer, $borderStyle, &$rowIndex): void {
            $rowData = [];

            // Crear estilo para las filas de datos (alternadas)
            $dataStyle = new Style();
            $dataStyle->setBorder($borderStyle);
            $dataStyle->setCellAlignment(CellAlignment::CENTER);

            // Alternar color de fondo para filas pares
            if ($rowIndex % 2 === 0) {
                $dataStyle->setBackgroundColor('F2F2F2');
            }

            // Crear un estilo especial para las columnas numéricas
            $numericStyle = clone $dataStyle;
            $numericStyle->setCellAlignment(CellAlignment::RIGHT);

            foreach (array_keys($this->columns) as $column) {
                $value = $record->{$column} ?? '';

                switch ($column) {
                    case 'cuil':
                        if (strlen($value) >= 3) {
                            $value = substr($value, 2, -1);
                        }
                        break;

                    case 'impp_conce':
                        $value = is_numeric($value) ? $value : 0;
                        $value = number_format((float) $value, 2, ',', '.');
                        break;

                    case 'nro_liqui':
                    case 'nro_legaj':
                        $value = (string) $value;
                        break;
                }

                $rowData[] = $value;
            }

            // Agregar la fila al archivo Excel
            $row = Row::fromValues($rowData, $dataStyle);
            $writer->addRow($row);

            $rowIndex++;
        });
    }

    protected function writeSummarySheet(Writer $writer): void
    {
        // Crear estilos para la hoja de resumen
        $headerStyle = new Style();
        $headerStyle->setFontBold();
        $headerStyle->setFontColor(Color::WHITE);
        $headerStyle->setBackgroundColor('4472C4');
        $headerStyle->setCellAlignment(CellAlignment::CENTER);

        $titleStyle = new Style();
        $titleStyle->setFontBold();
        $titleStyle->setFontSize(14);

        $subtitleStyle = new Style();
        $subtitleStyle->setFontBold();
        $subtitleStyle->setFontSize(12);

        $dataStyle = new Style();
        $dataStyle->setCellAlignment(CellAlignment::LEFT);

        $numericStyle = new Style();
        $numericStyle->setCellAlignment(CellAlignment::RIGHT);

        // Título de la hoja
        $writer->addRow(Row::fromValues(['RESUMEN DE REPORTE'], $titleStyle));
        $writer->addRow(Row::fromValues([''])); // Fila en blanco

        // Información general
        $writer->addRow(Row::fromValues(['Total de registros:', $this->summaryData['totalRegistros']], $dataStyle));
        $writer->addRow(Row::fromValues(['Importe total:', number_format($this->summaryData['totalGeneral'], 2, ',', '.')], $dataStyle));
        $writer->addRow(Row::fromValues([''])); // Fila en blanco

        // Tabla de resumen por dependencia
        $writer->addRow(Row::fromValues(['Resumen por Dependencia'], $subtitleStyle));
        $writer->addRow(Row::fromValues([''])); // Fila en blanco

        // Cabecera de la tabla
        $writer->addRow(Row::fromValues(['Dependencia', 'Registros', 'Importe'], $headerStyle));

        // Datos de la tabla
        foreach ($this->summaryData['totalsByDependency'] as $index => $dep) {
            $rowStyle = new Style();
            if ($index % 2 === 0) {
                $rowStyle->setBackgroundColor('F2F2F2');
            }

            $writer->addRow(Row::fromValues([
                $dep['dependencia'],
                $dep['registros'],
                number_format($dep['total'], 2, ',', '.'),
            ], $rowStyle));
        }
    }

    protected function prepareSummaryData(): void
    {
        // Calcular resumen de manera optimizada usando cursor
        $totalGeneral = 0.0;
        /** @var array<string, array{dependencia: string, total: float, registros: int}> $dependencyTotals */
        $dependencyTotals = [];
        $totalRegistros = 0;

        foreach ($this->query->cursor() as $lazyCollection) {
            $totalGeneral += $lazyCollection->impp_conce;
            $totalRegistros++;

            if (!isset($dependencyTotals[$lazyCollection->codc_uacad])) {
                $dependencyTotals[$lazyCollection->codc_uacad] = [
                    'dependencia' => $lazyCollection->codc_uacad,
                    'total' => 0,
                    'registros' => 0,
                ];
            }

            $dependencyTotals[$lazyCollection->codc_uacad]['total'] += $lazyCollection->impp_conce;
            $dependencyTotals[$lazyCollection->codc_uacad]['registros']++;
        }

        // Ordenar por total descendente
        /** @phpstan-ignore-next-line argument.unresolvableType */
        uasort($dependencyTotals, fn(array $a, array $b): int => $b['total'] <=> $a['total']);

        /** @var list<array{dependencia: string, total: float, registros: int}> $totalsList */
        /** @phpstan-ignore argument.unresolvableType */
        $totalsList = array_values($dependencyTotals);
        $this->summaryData = [
            'totalGeneral' => $totalGeneral,
            'totalsByDependency' => $totalsList,
            'totalRegistros' => $totalRegistros,
        ];
    }
}
