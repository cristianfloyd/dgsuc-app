<?php

namespace App\Models;

use Exception;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use App\Services\DataMapperService;
use Illuminate\Support\Facades\Log;
use App\Services\FileProcessorService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


/**
 * Modelo para representar los datos de AFIP SICOSS desde Mapuche.
 *
 * @property string $periodo_fiscal
 * @property string $cuil
 */
class AfipSicossDesdeMapuche extends Model
{

    use MapucheConnectionTrait;


    const string TABLE_NAME = 'suc.afip_mapuche_sicoss';

    protected $table = self::TABLE_NAME;

    // Definir la clave primaria compuesta entre el perido_fiscal y el cuil
    protected $primaryKey = ['periodo_fiscal', 'cuil'];
    // Asegurar que la clave primaria no es autoincremental
    public $incrementing = false;


    // Agregar las columnas que pueden ser asignadas masivamente
    protected $fillable = [
        'periodo_fiscal',
        'cuil',
        'apnom',
        'conyuge',
        'cant_hijos',
        'cod_situacion',
        'cod_cond',
        'cod_act',
        'cod_zona',
        'porc_aporte',
        'cod_mod_cont',
        'cod_os',
        'cant_adh',
        'rem_total',
        'rem_impo1',
        'asig_fam_pag',
        'aporte_vol',
        'imp_adic_os',
        'exc_aport_ss',
        'exc_aport_os',
        'prov',
        'rem_impo2',
        'rem_impo3',
        'rem_impo4',
        'cod_siniestrado',
        'marca_reduccion',
        'recomp_lrt',
        'tipo_empresa',
        'aporte_adic_os',
        'regimen',
        'sit_rev1',
        'dia_ini_sit_rev1',
        'sit_rev2',
        'dia_ini_sit_rev2',
        'sit_rev3',
        'dia_ini_sit_rev3',
        'sueldo_adicc',
        'sac',
        'horas_extras',
        'zona_desfav',
        'vacaciones',
        'cant_dias_trab',
        'rem_impo5',
        'convencionado',
        'rem_impo6',
        'tipo_oper',
        'adicionales',
        'premios',
        'rem_dec_788_05',
        'rem_imp7',
        'nro_horas_ext',
        'cpto_no_remun',
        'maternidad',
        'rectificacion_remun',
        'rem_imp9',
        'contrib_dif',
        'hstrab',
        'seguro',
        'ley_27430',
        'incsalarial',
        'remimp11',
    ];

    // No necesitas usar timestamps
    public $timestamps = false;

    protected $contador = 0;
    protected $columnWidths = [
        // Anchos de columna para cada campo
        6, 11, 30, 1, 2, 2, 2, 3, 2, 5, 3, 6, 2, 12, 12, 9, 9, 9, 9, 9, 50, 12, 12, 12, 2, 1, 9, 1, 9, 1, 2, 2, 2, 2, 2, 2, 12, 12, 12, 12, 12, 9, 12, 1, 12, 1, 12, 12, 12, 12, 3, 12, 12, 9, 12, 9, 3, 1, 12, 12, 12
    ];
    protected $periodoFiscal = 202312;

    private FileProcessorService $fileProcessor;
    private DataMapperService $dataMapper;

    public function __construct(FileProcessorService $fileProcessor, DataMapperService $dataMapper)
    {
        parent::__construct();
        $this->fileProcessor = $fileProcessor;
        $this->dataMapper = $dataMapper;
    }

    // ##########################################################################
    // ############## METODOS  ##################################################
    // ##########################################################################

    /**
     * Procesa una tabla de líneas extraídas y devuelve una tabla procesada.
     *
     * @param array $lineasExtraidas Un array de líneas extraídas de una tabla de una sola columna y ancho fijo.
     * @return array Un array de líneas procesadas.
     * @throws InvalidArgumentException Si los parámetros de entrada no son válidos.
     */
    public function procesarTabla(array $lineasExtraidas, int $periodoFiscal): array
    {
        if($periodoFiscal){
            $this->periodoFiscal = $periodoFiscal;
        }
        // Validación de entrada
        if (empty($lineasExtraidas)){
            throw new InvalidArgumentException('Las líneas extraídas no pueden estar vacías.');
        }

        // Inicialización de la tabla procesada
        // es un array multidimensional
        $tablaProcesada = [];
        // Iteración sobre las líneas extraídas
        foreach ($lineasExtraidas as $key => $line) {
                // Procesamiento de cada línea
                $this->contador++;
                $tablaProcesada[$key] = $this->procesar($line, $this->columnWidths);
        }
        // Retorno de la tabla procesada
        return $tablaProcesada;
    }


    /**
     * Cuenta el número de caracteres en cada línea del archivo y devuelve el valor mínimo y máximo.
     *
     * @param string $filePath La ruta del archivo.
     * @return array Un array con los valores mínimo y máximo de caracteres por línea.
     * @throws \Exception Si no se puede abrir el archivo.
     */
    public function contarCaracteresPorLinea(string $filePath): array
    {
        // Abrir el archivo en modo lectura
        $archivo = fopen($filePath, "r");

        // Verificar que el archivo se abrió correctamente
        if (!$archivo) {
            throw new Exception("No se pudo abrir el archivo");
        }

        $minCaracteres = PHP_INT_MAX;
        $maxCaracteres = PHP_INT_MIN;
        $i = 0;
        $kmin = 0;
        $kmax = 0;
        // Leer el archivo línea por línea
        while (($linea = fgets($archivo)) !== false) {
            $numCaracteres = mb_strlen($linea);

            // Actualizar el mínimo y máximo de caracteres
            if ($numCaracteres < $minCaracteres) {
                $minCaracteres = $numCaracteres;
                $kmin = $i;
            }
            if ($numCaracteres > $maxCaracteres) {
                $maxCaracteres = $numCaracteres;
                $kmax = $i;
            }
            $i++;
        }

        // Cerrar el archivo
        fclose($archivo);

        // Devolver los valores mínimo y máximo
        return [
            $kmin => $minCaracteres,
            $kmax => $maxCaracteres
        ];
    }



    /**
     * Procesa una línea de datos y devuelve un array con los datos procesados.
     *
     * @param string $line La línea de datos a procesar.
     * @param array $columnWidths Los anchos de columna para cada campo.
     * @return array Un array con los datos procesados.
     */
    public function procesar(string $line, array $columnWidths): array
    {
        $processedLine = $this->processLine($line, $columnWidths);
        $lineaMapeada = $this->dataMapper->mapDataToModel($processedLine);
        // dd($processedLine);
        // Retorno de la línea procesada.
        return $lineaMapeada;
    }



    /**
     * Procesa una línea del archivo AFIP Mapuche SICOSS.
     *
     * Esta función recibe una línea del archivo y los anchos de las columnas, y devuelve los datos procesados.
     *
     * @param Request $line La solicitud HTTP que contiene la línea y los anchos de las columnas.
     * @return [] Los datos procesados en formato Array.
     */
    public function processLine(string $line,array $columnWidths):array
    {
        // Validacion de entrada.
        if (is_null($line) || is_null($columnWidths) || !is_array($columnWidths))
        {
            Throw new InvalidArgumentException('La linea de entrada y los Anchos de columna no pueden estar vacios.');
        }

        $datosProcesados = $this->procesarlineainterna($line, $columnWidths);
        // Retornar los datos procesados.
        return $datosProcesados;
    }


    /**
     * Procesa una línea interna de un archivo de datos.
     *
     * Esta función toma una línea de texto y un arreglo de anchos de columna, y devuelve un arreglo con los valores de cada columna procesados.
     *
     * @param string $line La línea de texto a procesar.
     * @param int[] $columnWidths Un arreglo con los anchos de cada columna.
     * @return string[] Un Array con los valores de cada columna procesados.
     */
    private function procesarlineainterna($line, $columnWidths):array
    {
        //Validad la entrada
        if(empty($line) || empty($columnWidths))
        {
            Throw new InvalidArgumentException('La linea de entrada y los Anchos de columna no pueden estar vacios.');
        }

         // Calcular el ancho total de la línea
        $anchoLinea = $this->calcularAnchoLinea($line) - 2 + 6; // -1 se le resta la ultima posicion de la fila. Y +6 por la columna del periodo fiscal.

        // Calcular la suma de los anchos de columna
        $sumaAnchoColumnas = array_sum($columnWidths); // -1 se resta la ultima posicion de la fila.

        // Validar que el ancho de la línea coincida con la suma de los anchos de columna
        if ($anchoLinea !== $sumaAnchoColumnas) {
            throw new InvalidArgumentException('linea: '.$this->contador  .' El ancho de línea '.$anchoLinea .' no coincide con la suma de los anchos de columna ' .$sumaAnchoColumnas);
        }


        $datosProcesados = [];
        $currentPosition = 0;
        $ultimaPosicion = $sumaAnchoColumnas;
        /***********************************
        * Recorre el array $columnWidths y para cada posicion
        * extrae el valor de la línea correspondiente al ancho de la columna
        * en la posicion actual de la iteración
        *
        */

        $firstIteration = true;
        foreach ($columnWidths as $index => $columnWidth) {
            $endPosition = $currentPosition + $columnWidth;
            if ($firstIteration) {
                $firstIteration = false; // Marcar que la primera iteración ha pasado
                $datosProcesados[$index] = $this->periodoFiscal; //
                continue; // Saltar a la siguiente iteración
            }
            $startPosition = $currentPosition; // el primer valor de $starPosition es 0
            $currentPosition += $columnWidth; // Avanzar el puntero para la siguiente iteración.

            //Extraer el valor de la columna de la linea de entrada
            $columnValue = mb_substr($line, $startPosition, $columnWidth);
            // dd($columnValue);
            $datosProcesados[$index] = $columnValue;
            // $currentPosition += $endPosition;
            // dd($currentPosition);
        }
        return $datosProcesados; // un Array con 61 posiciones.
    }

    /**
     * Calcula el ancho total de una línea de texto.
     *
     * @param string $line La línea de texto.
     * @return int El ancho total de la línea.
     */
    private function calcularAnchoLinea(string $line): int
    {
        return mb_strlen($line);
    }




    /* #################### FUNCIONES PARA IMPORTAR A LA BASE DE DATOS #################### */


    private function importFromFile($filePath, $periodoFiscal): bool
    {
        $this->fileProcessor->setPeriodoFiscal($periodoFiscal);
        $processedLines = $this->fileProcessor->processFile($filePath, $this->columnWidths);


        return self::insertBulkData( $processedLines->toArray() );
    }

    private function procesarDatos(array $lineasExtraidas, int $periodoFiscal): array
    {
        return array_map(function ($linea) use ($periodoFiscal) {
            $columnWidths = $this->columnWidths;
            $processedLine = $this->processLine($linea, $columnWidths);
            return $this->dataMapper->mapDataToModel($processedLine);
        }, $lineasExtraidas);
    }



    /**
     * Procesa los datos de la tabla afip_importacion_cruda y los inserta en la tabla afip_sicoss_desde_mapuche.
     * Mapea los datos procesados a los campos del modelo AfipSicossDesdeMapucheModel y los guarda en la base de datos.
     * Establece el periodo_fiscal como una constante '202405' para todos los registros.
     *
     * Este método crea una nueva instancia de la clase afipImportacionCrudaModel y recorre su tabla.
     *
     * @return void
     */
    public function processTable(): void
    {
        $AfipimportacionCruda = new afipImportacionCrudaModel();
        $this->iterarTabla($AfipimportacionCruda);
    }

    /**
     * Itera sobre una tabla de importación cruda de AFIP y procesa cada línea.
     *
     * @param afipImportacionCrudaModel $request El modelo de importación cruda de AFIP.
     * @return void
     */
    private function iterartabla(afipImportacionCrudaModel $request)
    {
        $AfipimportacionCruda = $request;
        foreach ($AfipimportacionCruda->all() as $lineaImportadaCruda)
        {
            $this->processRow($lineaImportadaCruda);
        }
    }

    /**
    * Procesa una línea de importación cruda y crea un nuevo registro en la tabla afip_sicoss_desde_mapuche.
    *
    * @param string $lineaImportacionCruda La línea de importación cruda a procesar.
    * @return void
    */
    private function processRow($lineaImportadaCruda):void
    {
        $columnWidths = [
            // Anchos de columna para cada campo
            6, 11, 30, 1, 2, 2, 2, 3, 2, 5, 3, 6, 2, 12, 12, 9, 9, 9, 9, 9, 50, 12, 12, 12, 2, 1, 9, 1, 9, 1, 2, 2, 2, 2, 2, 2, 12, 12, 12, 12, 12, 9, 12, 1, 12, 1, 12, 12, 12, 12, 3, 12, 12, 9, 12, 9, 3, 1, 12, 12, 12
        ];
        $datosProcessados = $this->processLine($lineaImportadaCruda, $columnWidths);
        $this->dataMapper->mapDataToModel($datosProcessados);

    }





    /**
     * Inserta datos en lotes en la tabla 'afip_sicoss_desde_mapuche'.
     *
     * Este método recibe un array de datos mapeados y los inserta en la tabla en
     * chunks de un tamaño configurable. Utiliza una transacción para garantizar
     * la integridad de los datos.
     *
     * @param array $mappedData Los datos mapeados a insertar.
     * @param int $chunkSize El tamaño de los chunks a insertar (predeterminado: 1000).
     * @return bool Verdadero si la inserción se realizó correctamente, falso en caso contrario.
     */
    private  function insertBulkData(array $mappedData, int $chunkSize = 1000): bool
    {
        // Nombre de la conexión de base de datos a utilizar
        $conexion = $this->connection;
        // Iniciar la transacion en la conexion especificada
        DB::connection($conexion)->beginTransaction();
        $chunkSize = 1000; // Tamaño del chunk de la base de datos

        //Eliminar la clave primaria temporalmente: afip_sicoss_desde_mapuche_pkey
        // DB::connection($conexion)->statement('ALTER TABLE afip_sicoss_desde_mapuche DROP CONSTRAINT afip_sicoss_desde_mapuche_pkey');
        foreach (array_chunk($mappedData, $chunkSize) as $chunk) {
            foreach ($chunk as &$data) {
                // Elimina la clave 'id' si existe en los datos
                unset($data['id']);
            }

            try {
                DB::connection($conexion)->table($this->getTable())->insert($chunk);
            } catch (Exception $e) {
                // Registro de depuración en caso de error
                Log::error('Error al insertar los datos: ' . $e->getMessage());
            }
        }
        // Reestablecer la clave primaria: afip_sicoss_desde_mapuche_pkey
        // DB::connection($conexion)->statement('ALTER TABLE afip_sicoss_desde_mapuche ADD CONSTRAINT afip_sicoss_desde_mapuche_pkey PRIMARY KEY (id)');
        // Confirmar la transaccion en la conexion especificada.
        DB::connection($conexion)->commit();
        return true;
    }



	/**
	 * @return mixed
	 */
	public function getPeriodoFiscal() {
		return $this->periodoFiscal;
	}

	/**
	 * @return mixed
	 */
	public function getTable() {
		return $this->table;
	}
}

