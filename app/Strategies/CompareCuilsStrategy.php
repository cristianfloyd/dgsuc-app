<?php

namespace App\Strategies;

use App\Contracts\CuilOperationStrategy;
use App\Contracts\CuilRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompareCuilsStrategy implements CuilOperationStrategy
{
    private $repository;

    private $perPage;

    public function __construct(CuilRepositoryInterface $repository, int $perPage = 10)
    {
        $this->repository = $repository;
        $this->perPage = $perPage;
    }

    public function execute()
    {
        try {
            return Cache::remember('cuils_not_in_afip', now()->addHours(1), function () {
                $cuils = $this->repository->getCuilsNotInAfip($this->perPage);

                Log::info('CompareCuils ejecutado exitosamente');

                return [
                    'cuils' => $cuils,
                    'success' => true,
                    'message' => 'Comparación de CUILs completada',
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error en CompareCuils: ' . $e->getMessage());
            return [
                'cuils' => [],
                'success' => false,
                'message' => 'Error al comparar CUILs',
            ];
        }
    }
}
