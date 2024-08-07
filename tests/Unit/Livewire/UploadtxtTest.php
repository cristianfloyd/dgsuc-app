<?php

namespace Tests\Unit\Livewire;

use App\Livewire\Uploadtxt;
use App\Repositories\FileUploadRepository;
use App\Repositories\UploadedFileRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

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
            ->willThrowException(new \Exception($errorMessage));

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                $callback();
            });

        Livewire::test(Uploadtxt::class)
            ->call('deleteFile', $fileId)
            ->assertDispatched('error', 'Error: ' . $errorMessage);
    }
}
