<?php
$testData = [
    [
        'nro_legaj' => 12345,
        'nombre_completo' => 'Juan Pérez',
        'codn_conce' => 269,
        'importe_descontado' => 1500.50,
        'nro_embargo' => 1,
        'nro_cargo' => 100,
        'caratula' => 'Caso Prueba',
        'codc_uacad' => 'FAC'
    ]
];

$testData2 = [
    [
      "nro_legaj" => 149639,
      "nombre_completo" => "FIGOLI                                     FABIAN GUSTAVO      ",
      "codn_conce" => 269,
      "importe_descontado" => 2000.0,
      "nro_embargo" => 39,
      "nro_cargo" => 355462,
      "caratula" => "PARTESANO ALEJANDRA JAQUELINE C/ FIGOLI FABIAN GUSTAVO S/ INCIDENTE DE AUMENTO DE CUOTA ALIMENTARIA.",
      "codc_uacad" => "RCX ",
    ],
    [
      "nro_legaj" => 159300,
      "nombre_completo" => "CAJUSO               PAIS BARBAZAN         PABLO JOSE          ",
      "codn_conce" => 269,
      "importe_descontado" => 750.0,
      "nro_embargo" => 77,
      "nro_cargo" => 111787,
      "caratula" => "CHOVANCEK SILVIA Y OTRO C/ CAJUSO PABLO JOSE S/ EJECUCION DE ALIMENTOS.-",
      "codc_uacad" => "VTX ",
    ]
];
// Importamos las clases necesarias
// Creamos una instancia del servicio
use App\Models\Reportes\EmbargoReportModel;
use App\Services\Reportes\EmbargoReportService;
$service = app(EmbargoReportService::class);
$reportData = $service->generateReport(3);
EmbargoReportModel::setReportData($reportData->toArray());


// Generamos el reporte para la liquidación 3

// Establecemos los datos en el modelo

// Verificamos los resultados
$results = EmbargoReportModel::all();

// Inspeccionamos la estructura
$results->first();
