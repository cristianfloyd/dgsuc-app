<?php

namespace App\Services;

use App\DTOs\ReportHeaderDTO;

class ReportHeaderService
{
    public function getReportHeader(int $liquidationNumber): ReportHeaderDTO
    {
        // Lógica para obtener la información del encabezado
        return new ReportHeaderDTO(
            logoPath: asset(path: 'storage/logo.png'),
            orderNumber: 'OP-' . uniqid(),
            liquidationNumber: (string) $liquidationNumber,
            liquidationDescription: 'Descripción de la liquidación',
            generationDate: now()->format('Y-m-d H:i:s')
        );
    }
}

