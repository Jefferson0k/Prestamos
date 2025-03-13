<?php

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
});

// Archivos de configuración adicionales
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
