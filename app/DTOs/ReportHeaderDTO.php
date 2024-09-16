<?php

namespace App\DTOs;

class ReportHeaderDTO
{
    public function __construct(
        public string $logoPath,
        public string $orderNumber,
        public string $liquidationNumber,
        public string $liquidationDescription,
        public string $generationDate
    ) {}
}
