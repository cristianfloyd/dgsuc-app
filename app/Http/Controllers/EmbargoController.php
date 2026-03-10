<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\EmbargoRepositoryInterface;
use App\Http\Requests\EmbargoRequest;
use Illuminate\Http\JsonResponse;

class EmbargoController extends Controller
{
    /**
     * @var EmbargoRepositoryInterface
     */
    protected EmbargoRepositoryInterface $embargoRepository;

    /**
     * Constructor del controller.
     *
     * @param EmbargoRepositoryInterface $embargoRepository
     */
    public function __construct(EmbargoRepositoryInterface $embargoRepository)
    {
        $this->embargoRepository = $embargoRepository;
    }

    /**
     * Procesa los embargos.
     *
     * @param EmbargoRequest $request
     *
     * @return JsonResponse
     */
    public function process(EmbargoRequest $request)
    {
        $results = $this->embargoRepository->executeEmbargoProcess(
            $request->nroComplementarias,
            $request->nroLiquiDefinitiva,
            $request->nroLiquiProxima,
            $request->insertIntoDh25,
        );

        return response()->json($results);
    }
}
