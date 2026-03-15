<?php

namespace Tests\Unit\Livewire;

use App\Contracts\FileUploadRepositoryInterface;
use App\Contracts\OrigenRepositoryInterface;
use App\Livewire\Uploadtxt;
use App\Models\UploadedFile;
use App\Services\FileUploadService;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class UploadtxtTest extends TestCase
{
    // use RefreshDatabase;

    private \PHPUnit\Framework\MockObject\MockObject&FileUploadRepositoryInterface $fileUploadRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileUploadRepository = $this->createMock(FileUploadRepositoryInterface::class);
        $this->app->instance(FileUploadRepositoryInterface::class, $this->fileUploadRepository);
    }

    public function testDeleteFileSuccess(): void
    {
        $fileId = 1;
        $file = (object) ['id' => $fileId, 'file_path' => '/path/to/file'];

        $this->fileUploadRepository->expects($this->once())
            ->method('findOrFail')
            ->with($fileId)
            ->willReturn($file);

        $this->fileUploadRepository->expects($this->once())
            ->method('delete')
            ->with($file);

        $fileUploadService = $this->createMock(FileUploadService::class);
        $fileUploadService->expects($this->once())
            ->method('deleteFile')
            ->with($file->file_path)
            ->willReturn(true);
        $this->app->instance(FileUploadService::class, $fileUploadService);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback): void {
                $callback();
            });

        Livewire::test(Uploadtxt::class)
            ->call('deleteFile', $fileId)
            ->assertDispatched('success');
    }

    public function testDeleteFileFailure(): void
    {
        $fileId = 1;
        $errorMessage = 'File not found';

        $this->fileUploadRepository->expects($this->once())
            ->method('findOrFail')
            ->with($fileId)
            ->willThrowException(new Exception($errorMessage));

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback): void {
                $callback();
            });

        Livewire::test(Uploadtxt::class)
            ->call('deleteFile', $fileId)
            ->assertDispatched('error', 'Error: ' . $errorMessage);
    }

    public function testSaveSuccess(): void
    {
        $mockFileUploadService = $this->createMock(FileUploadService::class);
        $mockFileUploadRepository = $this->createMock(FileUploadRepositoryInterface::class);
        $mockOrigenRepository = $this->createMock(OrigenRepositoryInterface::class);

        $mockFileUploadRepository->method('all')->willReturn(collect());
        $mockFileUploadRepository->expects($this->once())
            ->method('create')
            ->willReturn(new UploadedFile());
        $mockFileUploadRepository->method('existsByOrigen')->willReturn(false);

        $mockFileUploadService->expects($this->once())
            ->method('uploadFile')
            ->willReturn('/path/to/uploaded/file.txt');

        $mockOrigenRepository->expects($this->once())
            ->method('findByName')
            ->with('afip')
            ->willReturn((object) ['name' => 'afip']);

        $this->app->instance(FileUploadRepositoryInterface::class, $mockFileUploadRepository);
        $this->app->instance(FileUploadService::class, $mockFileUploadService);
        $this->app->instance(OrigenRepositoryInterface::class, $mockOrigenRepository);

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('test.txt');

        Livewire::test(Uploadtxt::class)
            ->set('archivotxtAfip', $mockFile)
            ->set('periodo_fiscal', '202301')
            ->set('selectedLiquidacion', 1)
            ->set('processId', 'test-uuid')
            ->call('save', 'afip');
    }

    public function testSaveFailureFileUpload(): void
    {
        $mockFileUploadService = $this->createMock(FileUploadService::class);
        $mockFileUploadRepository = $this->createMock(FileUploadRepositoryInterface::class);
        $mockOrigenRepository = $this->createMock(OrigenRepositoryInterface::class);

        $mockFileUploadRepository->method('all')->willReturn(collect());
        $mockFileUploadRepository->method('existsByOrigen')->willReturn(false);

        $mockFileUploadService->expects($this->once())
            ->method('uploadFile')
            ->willReturn(false);

        $this->app->instance(FileUploadRepositoryInterface::class, $mockFileUploadRepository);
        $this->app->instance(FileUploadService::class, $mockFileUploadService);
        $this->app->instance(OrigenRepositoryInterface::class, $mockOrigenRepository);

        $mockFile = $this->createMock(UploadedFile::class);

        Livewire::test(Uploadtxt::class)
            ->set('archivotxtAfip', $mockFile)
            ->set('periodo_fiscal', '202301')
            ->set('selectedLiquidacion', 1)
            ->set('processId', 'test-uuid')
            ->call('save', 'afip')
            ->assertDispatched('fileUploadError');
    }

    public function testSaveFailureDatabaseInsert(): void
    {
        $mockFileUploadService = $this->createMock(FileUploadService::class);
        $mockFileUploadRepository = $this->createMock(FileUploadRepositoryInterface::class);
        $mockOrigenRepository = $this->createMock(OrigenRepositoryInterface::class);

        $mockFileUploadRepository->method('all')->willReturn(collect());
        $mockFileUploadRepository->expects($this->once())
            ->method('create')
            ->willReturn(false);
        $mockFileUploadRepository->method('existsByOrigen')->willReturn(false);

        $mockFileUploadService->expects($this->once())
            ->method('uploadFile')
            ->willReturn('/path/to/uploaded/file.txt');

        $mockOrigenRepository->expects($this->once())
            ->method('findByName')
            ->with('afip')
            ->willReturn((object) ['name' => 'afip']);

        $this->app->instance(FileUploadRepositoryInterface::class, $mockFileUploadRepository);
        $this->app->instance(FileUploadService::class, $mockFileUploadService);
        $this->app->instance(OrigenRepositoryInterface::class, $mockOrigenRepository);

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('test.txt');

        Livewire::test(Uploadtxt::class)
            ->set('archivotxtAfip', $mockFile)
            ->set('periodo_fiscal', '202301')
            ->set('selectedLiquidacion', 1)
            ->set('processId', 'test-uuid')
            ->call('save', 'afip')
            ->assertDispatched('fileUploadError');
    }
}
