<?php

namespace Tests\Unit;

use App\Models\UploadedFile;
use App\Services\TableManagementService;
use Illuminate\Http\UploadedFile as IlluminateUploadedFile;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use PHPUnit\Framework\TestCase;

class ImportServiceTest extends TestCase
{
    protected $importService;

    protected $tableManagementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tableManagementService = \Mockery::mock(TableManagementService::class);
        $this->importService = Livewire::ImportService($this->tableManagementService);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testImportFileSuccessfully(): void
    {
        $uploadedFile = \Mockery::mock(UploadedFile::class);
        $uploadedFile->file_path = '/ruta/al/archivo.csv';
        $uploadedFile->periodo_fiscal = '202301';
        $uploadedFile->id = 1;

        $this->tableManagementService->shouldReceive('verifyAndPrepareTable')
            ->once()
            ->with('suc.afip_mapuche_sicoss', null);

        $illuminateFile = \Mockery::mock(IlluminateUploadedFile::class);

        Log::shouldReceive('info')
            ->once()
            ->with('Archivo 1 importado a la tabla suc.afip_mapuche_sicoss.');

        $result = $this->importService->importFile($uploadedFile);

        $this->assertTrue($result);
    }

    public function testImportFileWithInvalidFile(): void
    {
        $uploadedFile = \Mockery::mock(UploadedFile::class);
        $uploadedFile->file_path = '/ruta/al/archivo_invalido.txt';
        $uploadedFile->periodo_fiscal = '202301';

        $this->tableManagementService->shouldReceive('verifyAndPrepareTable')
            ->once()
            ->with('suc.afip_mapuche_sicoss', null);

        $this->expectException(\Exception::class);

        $this->importService->importFile($uploadedFile);
    }

    public function testImportFileWithEmptyFile(): void
    {
        $uploadedFile = \Mockery::mock(UploadedFile::class);
        $uploadedFile->file_path = '/ruta/al/archivo_vacio.csv';
        $uploadedFile->periodo_fiscal = '202301';

        $this->tableManagementService->shouldReceive('verifyAndPrepareTable')
            ->once()
            ->with('suc.afip_mapuche_sicoss', null);

        $this->expectException(\Exception::class);

        $this->importService->importFile($uploadedFile);
    }
}
