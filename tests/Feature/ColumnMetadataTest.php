<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\services\ColumnMetadata;

class ColumnMetadataTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function it_resolves_column_metadata_with_widths()
    {
        $columnMetadata = app(ColumnMetadata::class);
        $this->assertInstanceOf(ColumnMetadata::class, $columnMetadata);
        $this->assertIsArray($columnMetadata->getWidths());
        $this->assertCount(20, $columnMetadata->getWidths());
    }
}
