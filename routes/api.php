<?php

use App\Http\Controllers\AplicarCombinacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::post('/aplicar-combinacion', [AplicarCombinacionController::class, 'aplicar']);
});
