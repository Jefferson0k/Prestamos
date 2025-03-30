<?php

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\PagosController;
use App\Http\Controllers\Api\PrestamosController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Web\ClienteWebController;
use App\Http\Controllers\Web\PagosWebController;
use App\Http\Controllers\Web\PrestamosWebController;
use App\Http\Controllers\Web\ReporteWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/clientes', [ClienteWebController::class, 'index'])->name('clientes.index');
    Route::get('/pagos', [PagosWebController::class, 'index'])->name('clientes.index');
    Route::get('/prestamos', [PrestamosWebController::class, 'index'])->name('clientes.index');
    Route::get('/reportes', [ReporteWebController::class, 'index'])->name('clientes.index');

    Route::prefix('cliente')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('api.cliente.index');
        Route::post('/', [ClienteController::class, 'store'])->name('api.clientes.store');
        Route::get('{id}', [ClienteController::class, 'show'])->name('api.clientes.show');
        Route::put('{id}', [ClienteController::class, 'update'])->name('api.clientes.update');
        Route::delete('{id}', [ClienteController::class, 'destroy'])->name('api.clientes.destroy');
    });

    Route::prefix('prestamo')->group(function () {
        Route::get('/', action: [PrestamosController::class, 'index'])->name('api.prestamo.index');
        Route::get('/cliente', action: [PrestamosController::class, 'indexcliente'])->name('api.prestamo.indexcliente');
        Route::post('/', [PrestamosController::class, 'store'])->name('api.prestamo.store');
        Route::get('{id}', [PrestamosController::class, 'show'])->name('api.prestamo.show');
        Route::put('{id}', [PrestamosController::class, 'update'])->name('api.prestamo.update');
        Route::delete('{id}', [PrestamosController::class, 'destroy'])->name('api.prestamo.destroy');
        Route::get('/{prestamo}/simulacion', [PrestamosController::class, 'simulacion'])->name('prestamos.simulacion');
    });

    Route::prefix('pago')->group(function () {
        Route::get('/', [PagosController::class, 'index'])->name('api.pago.index');
        Route::post('/', [PagosController::class, 'store'])->name('api.pago.store');
        Route::get('{id}', [PagosController::class, 'show'])->name('api.pago.show');
        Route::put('{id}', [PagosController::class, 'update'])->name('api.pago.update');
        Route::delete('{id}', [PagosController::class, 'destroy'])->name('api.pago.destroy');
        Route::get('/{prestamo}/cuotas', [PagosController::class, 'getCuotas'])->name('prestamos.cuotas');
    });

    Route::prefix('reporte')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('api.reporte.index');
        Route::get('{id}', [ReporteController::class, 'show'])->name('api.reporte.show');
    });
});

// Archivos de configuración adicionales
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
