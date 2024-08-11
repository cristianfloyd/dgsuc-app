<?php

namespace App\Contracts;

interface DataMapperInterface
{
    /**
    * Mapea los datos procesados al modelo AfipSicossDesdeMapuche.
    * @param array $datosProcessados Los datos procesados.
    * @return array Los datos mapeados al modelo AfipSicossDesdeMapuche.
    */
    public function mapDataToModel(array $datosProcesados):array;

    /**
    * Mapea los datos procesados al modelo AfipSicossDesdeMapuche.
    * @param array $datosProcessados Los datos procesados.
    * @return array Los datos mapeados al modelo AfipSicossDesdeMapuche.
    */
    public function mapearDatosAlModelo(array $datosProcesados):array;
    public function mapLineToDatabaseModel(array $line): array;
}
