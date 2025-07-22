<?php

namespace App\Providers;

use App\Contracts\FileUploadRepositoryInterface;
use App\Contracts\OrigenRepositoryInterface;
use App\Models\OrigenesModel;
use App\Repositories\FileUploadRepository;
use Illuminate\Support\ServiceProvider;

class AfipProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(FileUploadRepositoryInterface::class, FileUploadRepository::class);
        $this->app->bind(OrigenRepositoryInterface::class, OrigenesModel::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
