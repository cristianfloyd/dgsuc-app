<?php

namespace Tests\Unit\Livewire;

use App\Contracts\FileUploadRepositoryInterface;
use Exception;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Uploadtxt;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Services\FileUploadService;
use App\Repositories\FileUploadRepository;
use App\Contracts\OrigenRepositoryInterface;
use App\Repositories\UploadedFileRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UploadtxtTest extends TestCase
{
    use RefreshDatabase;

    private $fileUploadRepository;
    private $uploadtxt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileUploadRepository = $this->createMock(UploadedFileRepository::class);
        $this->uploadtxt = Livewire::Uploadtxt($this->fileUploadRepository);
    }

    public function testDeleteFileSuccess()
    {
        $fileId = 1;
        $file = (object)['id' => $fileId];

        $this->fileUploadRepository->expects($this->once())
            ->method('findOrFail')
            ->with($fileId)
            ->willReturn($file);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                $callback();
            });

        $this->uploadtxt->expects($this->once())
            ->method('deleteFileAndRecord')
            ->with($file);

        $this->uploadtxt->expects($this->once())
            ->method('handleSuccessfulDeletion');

        Livewire::test(Uploadtxt::class)
            ->call('deleteFile', $fileId);
    }

    public function testDeleteFileFailure()
    {
        $fileId = 1;
        $errorMessage = 'File not found';

        $this->fileUploadRepository->expects($this->once())
            ->method('findOrFail')
            ->with($fileId)
            ->willThrowException(new Exception($errorMessage));

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                $callback();
            });

        Livewire::test(Uploadtxt::class)
            ->call('deleteFile', $fileId)
            ->assertDispatched('error', 'Error: ' . $errorMessage);
    }

    public function testSaveSuccess()
    {
        $mockFileUploadService = $this->createMock(FileUploadService::class);
        $mockFileUploadRepository = $this->createMock(FileUploadRepositoryInterface::class);
        $mockOrigenRepository = $this->createMock(OrigenRepositoryInterface::class);

        $component = Livewire:: Uploadtxt($mockFileUploadRepository, $mockFileUploadService, $mockOrigenRepository);

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('test.txt');

        $component->archivotxt = $mockFile;
        $component->periodo_fiscal = '202301';
        $component->selectedOrigen = 1;

        $mockFileUploadService->expects($this->once())
            ->method('uploadFile')
            ->willReturn('/path/to/uploaded/file.txt');

        $mockOrigenRepository->expects($this->once())
            ->method('findById')
            ->willReturn((object)['name' => 'TestOrigen']);

        $mockFileUploadRepository->expects($this->once())
            ->method('create')
            ->willReturn(new UploadedFile());

        $component->expects($this->once())
            ->method('validateAndPrepare');

        $component->expects($this->once())
            ->method('updateWorkflowAndRedirect');

        $component->save();
        $component->assertStatus(200);
    }

    public function testSaveFailureFileUpload()
    {
        $mockFileUploadService = $this->createMock(FileUploadService::class);
        $mockFileUploadRepository = $this->createMock(UploadedFileRepository::class);
        $mockOrigenRepository = $this->createMock(OrigenRepositoryInterface::class);

        $component = Livewire::Uploadtxt($mockFileUploadRepository, $mockFileUploadService, $mockOrigenRepository);

        $mockFile = $this->createMock(UploadedFile::class);
        $component->archivotxt = $mockFile;

        $mockFileUploadService->expects($this->once())
            ->method('uploadFile')
            ->willReturn(false);

        $component->expects($this->once())
            ->method('validateAndPrepare');

        $component->expects($this->once())
            ->method('handleException')
            ->with($this->isInstanceOf(Exception::class));

        $component->save();
    }

    public function testSaveFailureDatabaseInsert()
    {
        $mockFileUploadService = $this->createMock(FileUploadService::class);
        $mockFileUploadRepository = $this->createMock(UploadedFileRepository::class);
        $mockOrigenRepository = $this->createMock(OrigenRepositoryInterface::class);

        $component = Livewire:: Uploadtxt($mockFileUploadRepository, $mockFileUploadService, $mockOrigenRepository);

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('test.txt');

        $component->archivotxt = $mockFile;
        $component->periodo_fiscal = '202301';
        $component->selectedOrigen = 1;

        $mockFileUploadService->expects($this->once())
            ->method('uploadFile')
            ->willReturn('/path/to/uploaded/file.txt');

        $mockOrigenRepository->expects($this->once())
            ->method('findById')
            ->willReturn((object)['name' => 'TestOrigen']);

        $mockFileUploadRepository->expects($this->once())
            ->method('create')
            ->willReturn(false);

        $component->expects($this->once())
            ->method('validateAndPrepare');

        $component->expects($this->once())
            ->method('handleException')
            ->with($this->isInstanceOf(Exception::class));

        $component->save();
    }
}
