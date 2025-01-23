<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmbargoRequest;
use App\Contracts\Repositories\EmbargoRepositoryInterface;

class EmbargoController extends Controller
{
    /**
     * @var EmbargoRepositoryInterface
     */
    protected EmbargoRepositoryInterface $embargoRepository;

    /**
     * Constructor del controller
     *
     * @param EmbargoRepositoryInterface $embargoRepository
     */
    public function __construct(EmbargoRepositoryInterface $embargoRepository)
    {
        $this->embargoRepository = $embargoRepository;
    }

    /**
     * Procesa los embargos
     *
     * @param EmbargoRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(EmbargoRequest $request)
    {
        $results = $this->embargoRepository->executeEmbargoProcess(
            $request->nroComplementarias,
            $request->nroLiquiDefinitiva,
            $request->nroLiquiProxima,
            $request->insertIntoDh25
        );

        return response()->json($results);
    }
}
