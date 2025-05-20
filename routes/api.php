<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\PrestamosController;
use App\Http\Controllers\Api\PagosController;
use App\Http\Controllers\Api\ReporteController;

Route::apiResource('cliente', ClienteController::class);
    Route::apiResource('prestamo', PrestamosController::class);
    Route::apiResource('pago', PagosController::class);
    Route::apiResource('reporte', ReporteController::class);

