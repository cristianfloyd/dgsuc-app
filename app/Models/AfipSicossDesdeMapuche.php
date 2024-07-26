<?php

namespace App\Models;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AfipSicossDesdeMapuche extends Model
{
    // Especificar la tabla
    protected $table = 'suc.afip_mapuche_sicoss';
    protected $connection = 'pgsql-mapuche';
    // Definir la clave primaria compuesta entre el perido_fiscal y el cuil
    protected $primaryKey = ['periodo_fiscal', 'CUIL'];
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



    // ##########################################################################
    // ############## METODOS  ##################################################
    // ##########################################################################

    /**
     * Procesa una tabla de líneas extraídas y devuelve una tabla procesada.
     *
     * @param array $lineasExtraidas Un array de líneas extraídas de una tabla de una sola columna y ancho fijo.
     * @return array Un array de líneas procesadas.
     * @throws \InvalidArgumentException Si los parámetros de entrada no son válidos.
     */
    public function procesarTabla(array $lineasExtraidas, int $periodoFiscal): array
    {
        if($periodoFiscal){
            $this->periodoFiscal = $periodoFiscal;
        }
        // Validación de entrada
        if (empty($lineasExtraidas)){
            throw new \InvalidArgumentException('Las líneas extraídas no pueden estar vacías.');
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
        $lineaProcesada = $this->procesarLinea($line, $columnWidths);
        $lineaMapeada = $this->mapearDatosAlModelo($lineaProcesada);
        // dd($lineaProcesada);
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
    public function procesarlinea(string $line,array $columnWidths):array
    {
        // Validacion de entrada.
        if (is_null($line) || is_null($columnWidths) || !is_array($columnWidths))
        {
            Throw new \InvalidArgumentException('La linea de entrada y los Anchos de columna no pueden estar vacios.');
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
            Throw new \InvalidArgumentException('La linea de entrada y los Anchos de columna no pueden estar vacios.');
        }

         // Calcular el ancho total de la línea
        $anchoLinea = $this->calcularAnchoLinea($line) - 2 + 6; // -1 se le resta la ultima posicion de la fila. Y +6 por la columna del periodo fiscal.
        // dd($anchoLinea);
        // Calcular la suma de los anchos de columna
        $sumaAnchoColumnas = array_sum($columnWidths); // -1 se resta la ultima posicion de la fila.
        // dd($sumaAnchoColumnas);
        // Validar que el ancho de la línea coincida con la suma de los anchos de columna
        if ($anchoLinea !== $sumaAnchoColumnas) {
            throw new \InvalidArgumentException('linea: '.$this->contador  .' El ancho de línea '.$anchoLinea .' no coincide con la suma de los anchos de columna ' .$sumaAnchoColumnas);
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

    public static function importarDesdeArchivo($filename, $periodoFiscal)
    {
        if (empty($filename) || empty($periodoFiscal)) {
            throw new \InvalidArgumentException('Los parámetros de entrada no pueden estar vacíos.');
        }
        $archivoRuta = $filename;
        $archivoRuta = Storage::path("/public/$archivoRuta");

        if (Storage::exists("/public/$filename"))
        {
            $filename = Storage::path("/public/$filename");
        } else {
            throw new \InvalidArgumentException('El archivo no existe.');
        }

        $model = new self();
        $model->periodoFiscal = $periodoFiscal;
        //probar si se puede leer el archivo almacenado en $filename
        if(is_readable($filename)){
            // $lineasExtraidas = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lineasExtraidas = [];
            $archivo = fopen($filename, "r");
            $i = 0;
            if ($archivo) {
                while (($linea = fgets($archivo)) !== false ) {
                    $i++;
                    $lineasExtraidas[] = $linea;
                }
                fclose($archivo);
            } else {
                throw new Exception('No se pudo abrir el archivo.');
            }
            $tablaProcesada = $model->procesarTabla($lineasExtraidas, $periodoFiscal);
        } else {
            //el archivo no es legible
            throw new \InvalidArgumentException('El archivo no es legible.');
        }

        return self::insertarDatosMasivos($tablaProcesada);
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
        $datosProcessados = $this->procesarLinea($lineaImportadaCruda, $columnWidths);
        $this->mapearDatosAlModelo($datosProcessados);

    }




    /**
     * Inserta datos masivamente en la tabla AfipSicossDesdeMapuche.
     *
     * @param array $datosMapeados
     * @param int $chunkSize
     * @return bool
     */
    public static function insertarDatosMasivos(array $datosMapeados, int $chunkSize = 1000): bool
    {
        //
        // Nombre de la conexión de base de datos a utilizar
        $conexion = 'pgsql-mapuche';
        // Iniciar la transacion en la conexion especificada
        DB::connection($conexion)->beginTransaction();
        $chunkSize = 1000; // Tamaño del chunk de la base de datos

        //Eliminar la clave primaria temporalmente: afip_sicoss_desde_mapuche_pkey
        // DB::connection($conexion)->statement('ALTER TABLE afip_sicoss_desde_mapuche DROP CONSTRAINT afip_sicoss_desde_mapuche_pkey');
        foreach (array_chunk($datosMapeados, $chunkSize) as $chunk) {
            foreach ($chunk as &$data) {
                // Elimina la clave 'id' si existe en los datos
                unset($data['id']);
            }
            // Registro de depuración antes de la inserción
            Log::info('Insertando chunk: ' . json_encode($chunk));
            try {
                DB::connection($conexion)->table((new self)->getTable())->insert($chunk);
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
    * Mapea los datos procesados al modelo AfipSicossDesdeMapuche.
    * @param array $datosProcessados Los datos procesados.
    * @return array Los datos mapeados al modelo AfipSicossDesdeMapuche.
    */
    private function mapearDatosAlModelo(array $datosProcesados):array
    {
        $datosMapeados = [
            'periodo_fiscal' => $datosProcesados[0],
            'cuil' => $datosProcesados[1],
            'apnom' => $datosProcesados[2],
            'conyuge' => $datosProcesados[3],
            'cant_hijos' => $datosProcesados[4],
            'cod_situacion' => $datosProcesados[5],
            'cod_cond' => $datosProcesados[6],
            'cod_act' => $datosProcesados[7],
            'cod_zona' => $datosProcesados[8],
            'porc_aporte' => $datosProcesados[9],
            'cod_mod_cont' => $datosProcesados[10],
            'cod_os' => $datosProcesados[11],
            'cant_adh' => $datosProcesados[12],
            'rem_total' => $datosProcesados[13],
            'rem_impo1' => $datosProcesados[14],
            'asig_fam_pag' => $datosProcesados[15],
            'aporte_vol' => $datosProcesados[16],
            'imp_adic_os' => $datosProcesados[17],
            'exc_aport_ss' => $datosProcesados[18],
            'exc_aport_os' => $datosProcesados[19],
            'prov' => $datosProcesados[20],
            'rem_impo2' => $datosProcesados[21],
            'rem_impo3' => $datosProcesados[22],
            'rem_impo4' => $datosProcesados[23],
            'cod_siniestrado' => $datosProcesados[24],
            'marca_reduccion' => $datosProcesados[25],
            'recomp_lrt' => $datosProcesados[26],
            'tipo_empresa' => $datosProcesados[27],
            'aporte_adic_os' => $datosProcesados[28],
            'regimen' => $datosProcesados[29],
            'sit_rev1' => $datosProcesados[30],
            'dia_ini_sit_rev1' => $datosProcesados[31],
            'sit_rev2' => $datosProcesados[32],
            'dia_ini_sit_rev2' => $datosProcesados[33],
            'sit_rev3' => $datosProcesados[34],
            'dia_ini_sit_rev3' => $datosProcesados[35],
            'sueldo_adicc' => $datosProcesados[36],
            'sac' => $datosProcesados[37],
            'horas_extras' => $datosProcesados[38],
            'zona_desfav' => $datosProcesados[39],
            'vacaciones' => $datosProcesados[40],
            'cant_dias_trab' => $datosProcesados[41],
            'rem_impo5' => $datosProcesados[42],
            'convencionado' => $datosProcesados[43],
            'rem_impo6' => $datosProcesados[44],
            'tipo_oper' => $datosProcesados[45],
            'adicionales' => $datosProcesados[46],
            'premios' => $datosProcesados[47],
            'rem_dec_788_05' => $datosProcesados[48],
            'rem_imp7' => $datosProcesados[49],
            'nro_horas_ext' => $datosProcesados[50],
            'cpto_no_remun' => $datosProcesados[51],
            'maternidad' => $datosProcesados[52],
            'rectificacion_remun' => $datosProcesados[53],
            'rem_imp9' => $datosProcesados[54],
            'contrib_dif' => $datosProcesados[55],
            'hstrab' => $datosProcesados[56],
            'seguro' => $datosProcesados[57],
            'ley_27430' => $datosProcesados[58],
            'incsalarial' => $datosProcesados[59],
            'remimp11' => $datosProcesados[60],
        ];
        return $datosMapeados;
    }
}

