<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\PrestamosController;
use App\Http\Controllers\Api\PagosController;
use App\Http\Controllers\Api\ReporteController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('prestamos', PrestamosController::class);
    Route::apiResource('pagos', PagosController::class);
    Route::apiResource('reportes', ReporteController::class);
});
