<?php

namespace App\Services;

class ColumnMetadata
{
     /** @var array<int, int> */
    private array $widthsAfip;
    private array $widthsMapuche;
    private string $currentSystem;

    /**
     * @var array<string, int> Mapeo de nombres de columnas a índices
     */
    private const array COLUMN_MAP = [
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
            6,  // periodo fiscal
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
    }

    public function setSystem(string $system): void
    {
        if (!in_array($system, ['afip', 'mapuche'])) {
            throw new \InvalidArgumentException('Sistema no válido');
        }
        $this->currentSystem = $system;
    }

    /**
     * Obtiene los anchos de todas las columnas
     *
     * @return array<int, int>
     */
    public  function getWidths(): array
    {
        return $this->currentSystem === 'afip' ? $this->widthsAfip : $this->widthsMapuche;
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
}
