<?php

namespace App\services;

class ColumnMetadata
{
    private $widths;
    public function __construct(array $widths)
    {
        $this->widths = $widths;
    }

    public function getWidths()
    {
        return $this->widths;
    }
}
