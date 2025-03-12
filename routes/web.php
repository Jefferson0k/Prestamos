<?php

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\PrestamosController;
use App\Http\Controllers\Api\PagosController;
use App\Http\Controllers\Api\ReporteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Ruta principal
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Ruta del dashboard con middleware de autenticación
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grupo de rutas protegidas con middleware de autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('clientes', [ClienteController::class, 'vista'])->name('clientes.index');
    Route::get('prestamos', [PrestamosController::class, 'vista'])->name('prestamos.index');
    Route::get('pagos', [PagosController::class, 'vista'])->name('pagos.index');
    Route::get('reportes', [ReporteController::class, 'vista'])->name('reportes.index');
});

// Archivos de configuración y autenticación
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
