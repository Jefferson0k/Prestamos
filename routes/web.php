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

    Route::group(['prefix' => 'clientes', 'as' => 'clientes.'], function () {
        Route::get('/', [ClienteController::class, 'vista'])->name('vista');
        Route::get('/list', [ClienteController::class, 'index'])->name('index');
        Route::post('/', [ClienteController::class, 'store'])->name('store');
        Route::get('{id}/edit', [ClienteController::class, 'edit'])->name('edit');
        Route::put('{id}', [ClienteController::class, 'update'])->name('update');
        Route::delete('{id}', [ClienteController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'prestamos', 'as' => 'prestamos.'], function () {
        Route::get('/', [PrestamosController::class, 'vista'])->name('vista');
        Route::get('/list', [PrestamosController::class, 'index'])->name('index');
        Route::post('/', [PrestamosController::class, 'store'])->name('store');
        Route::get('{id}/edit', [PrestamosController::class, 'edit'])->name('edit');
        Route::put('{id}', [PrestamosController::class, 'update'])->name('update');
        Route::delete('{id}', [PrestamosController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'pagos', 'as' => 'pagos.'], function () {
        Route::get('/', [PagosController::class, 'vista'])->name('vista');
        Route::get('/list', [PagosController::class, 'index'])->name('index');
        Route::post('/', [PagosController::class, 'store'])->name('store');
        Route::get('{id}/edit', [PagosController::class, 'edit'])->name('edit');
        Route::put('{id}', [PagosController::class, 'update'])->name('update');
        Route::delete('{id}', [PagosController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'reportes', 'as' => 'reportes.'], function () {
        Route::get('/', [ReporteController::class, 'vista'])->name('vista');
        Route::get('/list', [ReporteController::class, 'index'])->name('index');
        Route::post('/', [ReporteController::class, 'store'])->name('store');
        Route::get('{id}/edit', [ReporteController::class, 'edit'])->name('edit');
        Route::put('{id}', [ReporteController::class, 'update'])->name('update');
        Route::delete('{id}', [ReporteController::class, 'destroy'])->name('destroy');
    });
});

// Archivos de configuración y autenticación
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
