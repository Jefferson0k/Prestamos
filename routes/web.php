<?php

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ConsultasDni;
use App\Http\Controllers\Api\PagosController;
use App\Http\Controllers\Api\PrestamosController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\UsuariosController;
use App\Http\Controllers\Web\ClienteWebController;
use App\Http\Controllers\Web\PagosWebController;
use App\Http\Controllers\Web\PrestamosWebController;
use App\Http\Controllers\Web\ReporteWebController;
use App\Http\Controllers\Web\UsuarioWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/clientes', [ClienteWebController::class, 'index'])->name('clientes.index');
    Route::get('/pagos', [PagosWebController::class, 'index'])->name('pagos.index');
    Route::get('/prestamos', [PrestamosWebController::class, 'index'])->name('prestamos.index');
    Route::get('/reportes', [ReporteWebController::class, 'index'])->name('reportes.index');
    Route::get('/usuario', [UsuarioWebController::class,'index'])->name('usuario.index');
    Route::get('/consulta/{dni}', [ConsultasDni::class, 'consultar'])->name('clientes.consultar');

    Route::prefix('cliente')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('cliente.index');
        Route::post('/', [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::put('{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
        Route::delete('{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    });

    Route::prefix('prestamo')->group(function () {
        Route::get('/', action: [PrestamosController::class, 'index'])->name('api.prestamo.index');
        Route::get('/cliente', action: [PrestamosController::class, 'indexcliente'])->name('api.prestamo.indexcliente');
        Route::post('/', [PrestamosController::class, 'store'])->name('prestamo.store');
        Route::get('{prestamo}', [PrestamosController::class, 'show'])->name('prestamo.show');
        Route::put('{prestamo}', [PrestamosController::class, 'update'])->name('prestamo.update');
        Route::delete('{prestamo}', [PrestamosController::class, 'destroy'])->name('prestamo.destroy');
        Route::get('/{prestamo}/simulacion', [PrestamosController::class, 'simulacion'])->name('prestamos.simulacion');
    });

    Route::prefix('pago')->group(function () {
        Route::get('/', [PagosController::class, 'index'])->name('pago.index');
        Route::post('/', [PagosController::class, 'store'])->name('pago.store');
        Route::get('/{id}', [PagosController::class, 'show'])->name('pago.show');
        Route::put('/{id}', [PagosController::class, 'update'])->name('pago.update'); 
        Route::delete('/{id}', [PagosController::class, 'destroy'])->name('pago.destroy');
        Route::get('/{prestamo}/cuotas', [PagosController::class, 'getCuotas'])->name('pago.getCuotas'); 
        Route::get('/create', [PagosController::class, 'create'])->name('pago.create'); 
    });   

    Route::prefix('reporte')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('reporte.index');
        Route::get('{id}', [ReporteController::class, 'show'])->name('reporte.show');
    }); 

    Route::prefix('usuarios')->group(function(){
        Route::get('/', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/',[UsuariosController::class, 'store'])->name('usuarios.store');
        Route::get('/{id}',[UsuariosController::class, 'show'])->name('usuarios.show');
        Route::put('/{id}',[UsuariosController::class, 'update'])->name('usuarios.update');
        Route::delete('/{id}',[UsuariosController::class, 'destroy'])->name('usuarios.destroy');
    });
}); 
// Archivos de configuración adicionales
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
