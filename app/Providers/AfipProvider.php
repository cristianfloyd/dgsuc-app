<?php

namespace App\Providers;

use App\Models\UploadedFile;
use App\Models\OrigenesModel;
use Illuminate\Support\ServiceProvider;
use App\Contracts\OrigenRepositoryInterface;
use App\Repositories\UploadedFileRepository;
use App\Contracts\FileUploadRepositoryInterface;

class AfipProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(FileUploadRepositoryInterface::class, UploadedFileRepository::class);
        $this->app->bind(OrigenRepositoryInterface::class, OrigenesModel::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
