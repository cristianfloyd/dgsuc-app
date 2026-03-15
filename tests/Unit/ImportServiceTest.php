<?php

namespace Tests\Unit;

use App\ImportService;
use App\Models\UploadedFile;
use App\Services\TableManagementService;
use Exception;
use Illuminate\Http\UploadedFile as IlluminateUploadedFile;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\TestCase;

class ImportServiceTest extends TestCase
{
    protected $importService;

    protected $tableManagementService;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var TableManagementService $tableManagementService */
        $tableManagementService = Mockery::mock(TableManagementService::class);
        $this->tableManagementService = $tableManagementService;
        $this->importService = new ImportService($tableManagementService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testImportFileSuccessfully(): void
    {
        $mock = Mockery::mock(UploadedFile::class);
        $mock->file_path = '/ruta/al/archivo.csv';
        $mock->periodo_fiscal = '202301';
        $mock->id = 1;

        $this->tableManagementService->shouldReceive('verifyAndPrepareTable')
            ->once()
            ->with('suc.afip_mapuche_sicoss', null);

        Mockery::mock(IlluminateUploadedFile::class);

        Log::shouldReceive('info')
            ->once()
            ->with('Archivo 1 importado a la tabla suc.afip_mapuche_sicoss.');

        $result = $this->importService->importFile($mock);

        $this->assertTrue($result);
    }

    public function testImportFileWithInvalidFile(): void
    {
        $mock = Mockery::mock(UploadedFile::class);
        $mock->file_path = '/ruta/al/archivo_invalido.txt';
        $mock->periodo_fiscal = '202301';

        $this->tableManagementService->shouldReceive('verifyAndPrepareTable')
            ->once()
            ->with('suc.afip_mapuche_sicoss', null);

        $this->expectException(Exception::class);

        $this->importService->importFile($mock);
    }

    public function testImportFileWithEmptyFile(): void
    {
        $mock = Mockery::mock(UploadedFile::class);
        $mock->file_path = '/ruta/al/archivo_vacio.csv';
        $mock->periodo_fiscal = '202301';

        $this->tableManagementService->shouldReceive('verifyAndPrepareTable')
            ->once()
            ->with('suc.afip_mapuche_sicoss', null);

        $this->expectException(Exception::class);

        $this->importService->importFile($mock);
    }
}
