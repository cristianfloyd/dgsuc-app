<?php

namespace App\Http\Controllers;

use App\Services\CheFileGenerator;
use Illuminate\Http\Request;

class CheController extends Controller
{
    private $cheGenerator;

    public function __construct(CheFileGenerator $cheGenerator)
    {
        $this->cheGenerator = $cheGenerator;
    }

    public function generate(Request $request)
    {
        $content = $this->cheGenerator->generateCheContent(
            $request->liquidaciones,
            $request->anio,
            $request->mes,
            $request->indice,
        );

        // Aquí manejas la respuesta según necesites
        return response()->json($content);
    }

    public function processChe(Request $request)
    {
        $result = $this->cheGenerator->sendCheToPilaga(
            $request->liquidaciones,
            $request->anio,
            $request->mes,
            $request->indice,
        );

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $comprobantes = $this->cheGenerator->processAndStore(
            $request->liquidaciones,
            $request->anio,
            $request->mes,
        );

        return response()->json([
            'message' => 'Comprobantes generados exitosamente',
            'data' => $comprobantes,
        ]);
    }
}
