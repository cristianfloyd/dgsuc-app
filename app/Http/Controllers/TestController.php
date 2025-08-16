<?php

namespace App\Http\Controllers;

use App\Services\ColumnMetadata;

class TestController extends Controller
{
    public function testColumnMetadata(): void
    {
        $columnMetadata = app(ColumnMetadata::class);
        dump($columnMetadata->getWidths());
    }
}
