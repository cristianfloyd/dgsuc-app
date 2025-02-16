<?php

namespace App\Services;

class ColumnMetadata
{
     /** @var array<int, int> */
    private array $widthsAfip;
    private array $widthsMapuche;
    private array $widthsMiSimplificacion;
    private string $currentSystem;
    private array $widthsSicossCalculo;
    private array $startPositionsSicossCalculo;

    /**
     * @var array<string, int> Mapeo de nombres de columnas a índices
     */
    private const COLUMN_MAP = [
        'PERIODO_FISCAL' => 0,
        'CODIGO_MOVIMIENTO' => 1,
        'TIPO_REGISTRO' => 2,
        'CUIL_EMPLEADO' => 3,
        'MARCA_TRABAJADOR_AGROPECUARIO' => 4,
        'MODALIDAD_CONTRATO' => 5,
        'FECHA_INICIO_RELACION_LABORAL' => 6,
        'FECHA_FIN_RELACION_LABORAL' => 7,
        'CODIGO_OBRA_SOCIAL' => 8,
        'CODIGO_SITUACION_BAJA' => 9,
        'FECHA_TELEGRAMA_RENUNCIA' => 10,
        'RETRIBUCION_PACTADA' => 11,
        'MODALIDAD_LIQUIDACION' => 12,
        'SUCURSAL_DOMICILIO_DESEMPENO' => 13,
        'ACTIVIDAD_DOMICILIO_DESEMPENO' => 14,
        'PUESTO_DESEMPENADO' => 15,
        'RECTIFICACION' => 16,
        'NUMERO_FORMULARIO_AGROPECUARIO' => 17,
        'TIPO_SERVICIO' => 18,
        'CATEGORIA_PROFESIONAL' => 19,
        'CODIGO_CONVENIO_COLECTIVO' => 20,
        'SIN_VALORES' => 21
    ];


    public function __construct()
    {
        $this->initializeWidths();
        $this->currentSystem = 'afip'; // Por defecto
    }

    /**
     * Inicializa los anchos de las columnas
     */
    private function initializeWidths(): void
    {

        $this->widthsAfip = [
            6,  // periodo fiscal (no se utiliza en el archivo TXT)
            2,  // codigo movimiento
            2,  // Tipo de registro
            11, // CUIL del empleado
            1,  // Marca de trabajador agropecuario
            3,  // Modalidad de contrato
            10, // Fecha de inicio de la rel. Laboral
            10, // Fecha de fin relacion laboral
            6,  // Código de obra social
            2,  // codigo situacion baja
            10, // Fecha telegrama renuncia
            15, // Retribución pactada
            1,  // Modalidad de liquidación
            5,  // Sucursal-Domicilio de desempeño
            6,  // Actividad en el domicilio de desempeño
            4,  // Puesto desempeñado
            1,  // Rectificación
            10, // Numero Formulario Agropecuario
            3,  // Tipo de Servicio
            6,  // Categoría Profesional
            7,  // Código de Convenio Colectivo de Trabajo
            4,  // Sin valores, en blanco
        ];

        $this->widthsMapuche = [
            // Anchos pra Mapuche
            6, 11, 30, 1, 2, 2, 2, 3, 2, 5, 3, 6, 2, 12, 12, 9, 9, 9, 9, 9, 50, 12, 12, 12, 2, 1, 9, 1, 9, 1, 2, 2, 2, 2, 2, 2, 12, 12, 12, 12, 12, 9, 12, 1, 12, 1, 12, 12, 12, 12, 3, 12, 12, 9, 12, 9, 3, 1, 12, 12, 12
        ];

        $this->widthsMiSimplificacion = [
            // Anchos pra Mi Simplificacion
            2, // codigo movimiento
            2,
            11,
            1,
            3,
            10,
            10,
            6,
            2,
            10,
            15,
            1,
            5,
            6,
            4,
            2,
            10,
            6,
            3,
            10,
            10,
            1
        ];

        $this->widthsSicossCalculo = [
            11,  // cuil (posicion 1)
            15,  // remtotal (posicion 76)
            15,  // remimpo1 (posicion 91)
            15,  // remimpo2 (posicion 106)
            15,  // aportesijp (posicion 136)
            15,  // aporteinssjp (posicion 151)
            15,  // contribucionsijp (posicion 301)
            15,  // contribucioninssjp (posicion 316)
            15,  // aportediferencialsijp (posicion 166)
            15,  // aportesres33_41re (posicion 1196)
        ];

        $this->startPositionsSicossCalculo = [
            'cuil' => 1,
            'remtotal' => 76,
            'rem1' => 91,
            'rem2' => 106,
            'aportesijp' => 136,
            'aporteinssjp' => 151,
            'contribucionsijp' => 301,
            'contribucioninssjp' => 316,
            'aportediferencialsijp' => 166,
            'aportesres33_41re' => 196,
        ];
    }

    public function setSystem(string $system): void
    {
        if (!in_array($system, ['afip', 'mapuche', 'miSimplificacion', 'sicossCalculo'])) {
            throw new \InvalidArgumentException('Sistema no válido');
        }
        $this->currentSystem = $system;
    }

    /**
     * Obtiene los anchos de todas las columnas
     *
     * @return array<int, int>
     */
    public function getWidths(): array
    {
        switch ($this->currentSystem) {
            case 'afip':
                return $this->widthsAfip;
            case 'mapuche':
                return $this->widthsMapuche;
            case 'miSimplificacion':
                return $this->widthsMiSimplificacion;
            case 'sicossCalculo':
                return $this->widthsSicossCalculo;
            default:
                throw new \InvalidArgumentException('Sistema no válido');
        }
    }

    /**
     * Obtiene el ancho de una columna específica.
     *
     * @param int $index Índice de la columna
     * @return int Ancho de la columna
     */
    public function getColumnWidth(int $index): int
    {
        $widths = $this->getWidths();
        return $widths[$index] ?? 0;
    }

    /**
     * Establece el ancho de una columna específica
     *
     * @param int|string $identifier Índice o nombre de la columna
     * @param int $width Nuevo ancho de la columna
     */
    public function setColumnWidth(int|string $identifier, int $width): void
    {
        $index = is_string($identifier) ? self::COLUMN_MAP[$identifier] : $identifier;
        $this->widthsAfip[$index] = $width;
    }

    /**
     * Calcula el ancho total de todas las columnas
     *
     * @return int Suma total de los anchos de todas las columnas
     */
    public function getTotalWidth(): int
    {
        return array_sum($this->widthsAfip);
    }

    /**
     * Obtiene la posición de inicio de un campo específico en el sistema 'sicossCalculo'.
     *
     * @param string $field Nombre del campo
     * @return int Posición de inicio del campo, o 0 si no se encuentra
     */
    public function getStartPosition(string $field): int
    {
        return $this->startPositionsSicossCalculo[$field] ?? 0;
    }
}
