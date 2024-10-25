<?php

namespace App\Services;

use Exception;
use RuntimeException;
use App\Models\UploadedFile;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use App\Contracts\DataMapperInterface;
use App\Services\AbstractFileProcessor;
use Illuminate\Support\Facades\Storage;
use App\Contracts\FileProcessorInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Procesa un archivo subido UploadedFile y devuelve un array de líneas procesadas.
 *
 * @param UploadedFile $file El archivo subido a procesar.
 * @param array $columnWidths Una matriz de anchos de columna para usar al procesar cada línea.
 * @return array Un array de líneas procesadas.
 */
class FileProcessorService extends AbstractFileProcessor implements FileProcessorInterface
{
    private const string UTF8_ENCODING = 'UTF-8';

    private int $periodoFiscal;
    private string $absolutePath;
    private string $tableName;


    public function __construct(
        private DatabaseService $databaseService,
        private ColumnMetadata $columnMetadata,
        private DataMapperInterface $dataMapper,
        int $periodoFiscal = 0
    ) {
        $this->periodoFiscal = $periodoFiscal;

    }

    public function setPeriodoFiscal(int $periodoFiscal): void
    {
        $this->periodoFiscal = $periodoFiscal;
    }



    /**
     * Maneja la importación de un archivo subido.
     *
     * Este método se encarga de asignar los valores del archivo subido, validar el archivo, establecer el sistema y procesar el archivo utilizando el servicio de procesamiento de archivos.
     * Finalmente, mapea los datos procesados y los devuelve como una colección.
     *
     * @param UploadedFile $file El archivo subido a procesar.
     * @param string $system El sistema al que pertenece el archivo.
     * @return Collection Una colección con los datos procesados del archivo.
     */
    public function handleFileImport(UploadedFile $file, string $system): Collection
    {
        Log::info('Asignando variables');;
        $this->assignValues($file);
        $filePath = $file->file_path;

        try {
            $this->validateInput($filePath, $this->periodoFiscal);
        } catch (Exception $e) {
            Log::error('Error al validar el archivo: ' . $e->getMessage());
            return new Collection();
        }
        $this->columnMetadata->setSystem($system);

        // Validación de $filePatfh, que pueda abrirse y leerse
        if (!is_readable($this->absolutePath)) {
            Log::error('El archivo no se puede leer.');
            throw new RuntimeException('El archivo no se puede leer.');
        }
        Log::info("Archivo válido para lectura.: $this->absolutePath");

        try {
            $columnWidths = $this->columnMetadata->getWidths();
            Log::info('columnWidths: ',[json_encode($columnWidths)]);
            Log::info("filepath para processFile: $filePath");

            $processedLines = $this->processFile($filePath, $columnWidths);
            $mappedData = $this->mapearDatos($processedLines, $system);

            Log::info('Datos mapeados handleFileImport:', [$mappedData->count()]);
            return  $mappedData;
        } catch (Exception $e) {
            // Manejar el error, posiblemente registrándolo
            Log::error('Error al procesar el archivo (handleFileImport): ' . $e->getMessage());
            return new Collection();
        }
    }

    /**
     * Mapea los datos procesados de un archivo según el sistema especificado.
     *
     * Este método se encarga de seleccionar el método de mapeo adecuado en función del sistema proporcionado.
     * Si el sistema es 'mapuche', se utilizará el método `mapearDatosMapucheSicoss()`.
     * Si el sistema es 'afip', se utilizará el método `mapearDatosRelacionesActivas()`.
     * Si el sistema no es válido, se lanzará una excepción.
     *
     * @param Collection $processedLines Las líneas procesadas del archivo.
     * @param string $system El sistema al que pertenece el archivo.
     * @return Collection Una colección con los datos mapeados.
     * @throws RuntimeException Si el sistema proporcionado no es válido.
     */
    private function mapearDatos(Collection $processedLines, $system = 'mapuche'): Collection
    {
        if ($system === 'mapuche') {
            return $this->mapearDatosMapucheSicoss($processedLines);
        } elseif ($system === 'afip') {
            return $this->mapearDatosRelacionesActivas($processedLines);
        } else {
            throw new RuntimeException('Sistema no válido: ' . $system);
        }
    }

    private function mapearDatosRelacionesActivas(Collection $processedLines): Collection
    {
        $mappedData = $processedLines
            ->map(fn($linea) => $this->dataMapper->mapDataToModelAfipRelacionesActivas($linea->toArray()));
        return new Collection($mappedData);
    }

    private function mapearDatosMapucheSicoss(Collection $processedLines): Collection
    {
        $mappedData = $processedLines
            ->map(fn($linea) => $this->dataMapper->mapDataToModel($linea->toArray()));
        return new Collection($mappedData);
    }


    /**
     * Obtiene los detalles de un archivo cargado.
     *
     * @param UploadedFile $file El archivo cargado para el que se deben obtener los detalles.
     * @return array Un array que contiene información sobre el archivo cargado, como la ruta del archivo, la ruta absoluta, el período fiscal y el nombre original del archivo.
     */
    public function getFileDetails(UploadedFile $file): array
    {
        return [
            'filepath' => $file->file_path,
            'absolutePath' => storage_path("app/{$file->file_path}"),
            'periodoFiscal' => $file->periodo_fiscal,
            'filename' => $file->original_name,
        ];
    }


    /**
     * Procesa un archivo dado utilizando los anchos de columna proporcionados.
     *
     * Este método lee cada línea del archivo, las procesa utilizando el método `processLine()` y devuelve un array con las líneas procesadas.
     *
     * @param string $filePath La ruta del archivo a procesar.
     * @param array $columnWidths Un array de anchos de columna a utilizar al procesar cada línea.
     * @param UploadedFile $uploadedFile El archivo subido a procesar.
     * @return Collection  Una Coleccion con las líneas del archivo procesadas.
     */
    public function processFile(string $filePath, array $columnWidths, UploadedFile $uploadedFile = null): Collection
    {
        if ($uploadedFile) {
            $this->assignValues($uploadedFile);
        }

        if (empty($this->periodoFiscal)) {
            Log::error('El periodo fiscal no está inicializado.');
            throw new RuntimeException('El periodo fiscal no está inicializado.');
        }

        Log::info("Procesando archivo: $filePath");
        try {
            // Use Storage facade to get the full path
            $fullPath = Storage::path($filePath);


            // Check if the file exists
            if (!Storage::exists($filePath)) {
                throw new RuntimeException("El archivo no existe: $filePath");
            }

            if($fileContent = Storage::get($filePath)){
                Log::info(sprintf('El archivo existe: %s', $filePath));
            };
            $encoding = $this->detectEncoding($fileContent);
            Log::info("Encoding detected: $encoding");

            $lines = $this->convertToUtf8($fileContent, $encoding);
            Log::info('Líneas del archivo: ' . $lines->count() );

            $mappedData = $this->processLines($lines->toArray(), $columnWidths);
            Log::info('Datos mapeados:', [$mappedData->count()]);
            return $mappedData;
        } catch (Exception $e) {
            Log::error('Error al procesar el archivo en FileProcessorService processFile(): ' . $e->getMessage());
            return new Collection();;
        }

        // Convertir la Illuminate\Support\Collection a Illuminate\Database\Eloquent\Collection
        // return new Collection($data->all());
    }

    /**
     * Lee el contenido del archivo.
     *
     * @param string $filePath
     * @return string
     */
    private function readFileContent(string $filePath): string
    {
        return file_get_contents(Storage::path($filePath));
    }

    /**
     * Detecta la codificación del contenido del archivo.
     *
     * @param string $content
     * @return string
     */
    private function detectEncoding(string $content): string
    {
        $encoding = mb_detect_encoding($content, mb_list_encodings(), true) ?: self::UTF8_ENCODING;
        return $encoding;
    }

    /**
     * Convierte el contenido del archivo a un array de líneas en formato UTF-8.
     *
     * @param string $content El contenido del archivo a convertir.
     * @param string|null $fromEncoding La codificación de origen del contenido, si se conoce. Si se omite, se intentará detectar automáticamente.
     * @return Collection Una colección de líneas en formato UTF-8.
     */
    private function convertToUtf8(string $content, string $fromEncoding = null): Collection
    {
        $utf8Content = mb_convert_encoding($content, self::UTF8_ENCODING, 'auto');


        $data = explode("\n", $utf8Content);
        return new Collection($data);
    }

    /**
     * Procesa las líneas del archivo.
     *
     * @param array $lines
     * @param array $columnWidths
     * @return Collection
     */
    private function processLines(array $lines, array $columnWidths): Collection
    {
        $data = collect($lines)
            ->filter()
            ->map(fn($line) => $this->processLine($line, $columnWidths));
        Log::info('Datos procesados en ProcessLines : ' . $data->count());
        return new Collection($data->all());
    }


    /**
     * Procesa una línea del archivo cargado utilizando los anchos de columna proporcionados y el período fiscal.
     *
     * @param string $line La línea del archivo a procesar.
     * @param array $columnWidths Un array de anchos de columna a utilizar al procesar la línea.
     * @return array $processedLines Un array de campos procesados de la línea.
     */
    protected function processLine(string $line, array $columnWidths): Collection
    {
        $posicion = 0;
        // Creamos una colección a partir de los anchos de columna
        $data = collect($columnWidths)
            ->map(function ($width, $key) use ($line, &$posicion) {
                if ($key === 0) {
                    return $campo = $this->periodoFiscal;
                }
                $campo = $this->processField($key, $line, $width, $posicion);
                $posicion += $width;
                return $campo;
            });
        // Convertir la Illuminate\Support\Collection a Illuminate\Database\Eloquent\Collection
        return new Collection($data->all());
    }

    /**
     * Procesa un campo individual de la línea.
     *
     * @param int $key El índice del campo.
     * @param string $line La línea completa del archivo.
     * @param int $width El ancho del campo.
     * @param int $posicion La posición inicial del campo en la línea.
     * @return string El campo procesado.
     */
    private function processField(int $key, string $line, int $width, int $posicion): string
    {
        if ($key === 0) {
            return $this->periodoFiscal;
        }
        $campo = substr($line, $posicion, $width);
        return str_replace(' ', ' ', $campo);
    }

    /**
     * Lee y extrae las líneas de un archivo dado.
     *
     * Este método abre el archivo en modo de lectura, lee cada línea del archivo y la convierte a UTF-8 si es necesario. Las líneas extraídas se devuelven en un array.
     *
     * @param string $filePath La ruta del archivo a leer.
     * @return array Las líneas extraídas del archivo.
     * @throws RuntimeException Si el archivo no se puede leer o abrir.
     */
    public function extractLines(string $filePath): Collection
    {
        $data = collect($this->readFileLines($filePath));
        return new Collection($data->all());
    }

    private function assignValues(UploadedFile $file): void
    {
        $fileDetails = $this->getFileDetails($file);
        $this->absolutePath = $fileDetails['absolutePath'];
        $this->periodoFiscal = $fileDetails['periodoFiscal'];
    }
    private function validateInput(string $filePath, int $periodoFiscal): void
    {
        // Verifica que los parámetros no estén vacíos.
        if (empty($filePath) || empty($periodoFiscal)) {
            throw new InvalidArgumentException('Los parámetros de entrada no pueden estar vacíos.');
        }

        // Comprueba que el archivo exista en el almacenamiento.
        if (!Storage::exists($filePath)) {
            throw new InvalidArgumentException("El archivo no existe: $filePath");
        }

        // Verifica que el archivo sea legible.
        $fullPath = Storage::path($filePath);
        if (!is_readable($fullPath)) {
            throw new InvalidArgumentException("El archivo no es legible: $fullPath");
        }

        // Valida que el periodo fiscal sea un valor razonable (mayor que 0 y no superior al año actual más 12 meses).
        if ($periodoFiscal <= 0 || $periodoFiscal > date('Y') * 100 + 12) {
            throw new InvalidArgumentException('El periodo fiscal no es válido.');
        }
    }
}
