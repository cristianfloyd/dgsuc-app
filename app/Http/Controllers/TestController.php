<?php

namespace App\Http\Controllers;

use App\services\ColumnMetadata;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testColumnMetadata()
    {
        $columnMetadata = app(ColumnMetadata::class);
        dump($columnMetadata->getWidths());
    }
}
