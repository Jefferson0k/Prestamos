<?php

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ConsultasDni;
use App\Http\Controllers\Api\CuotasController;
use App\Http\Controllers\Api\PagosController;
use App\Http\Controllers\Api\PrestamosController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UsuariosController;
use App\Http\Controllers\Web\ClienteWebController;
use App\Http\Controllers\Web\PagosWebController;
use App\Http\Controllers\Web\PrestamosWebController;
use App\Http\Controllers\Web\ReporteWebController;
use App\Http\Controllers\Web\UsuarioWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    #PARA QUE CUANDO SE CREA UN USUARIO O MODIFICA SU PASSWORD LO REDIRECCIONE PARA QUE PUEDA ACTUALIZAR
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return Inertia::render('Dashboard', [
            'mustReset' => $user->restablecimiento == 0,
        ]);
    })->name('dashboard');

    #VISTAS DEL FRONTEND
    Route::get('/clientes', [ClienteWebController::class, 'index'])->name('index.view');
    Route::get('/pagos', [PagosWebController::class, 'index'])->name('index.view');
    Route::get('/prestamos', [PrestamosWebController::class, 'index'])->name('index.view');
    Route::get('/reportes', [ReporteWebController::class, 'index'])->name('index.view');
    Route::get('/usuario', [UsuarioWebController::class,'index'])->name('index.view');
    Route::get('/consulta/{dni}', [ConsultasDni::class, 'consultar'])->name('consultar.view');
    Route::get('/roles', [UsuarioWebController::class, 'roles'])->name('roles.view');

    #CLIENTE => BACKEND
    Route::prefix('cliente')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('cliente.index');
        Route::post('/', [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::put('{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
        Route::delete('{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    });

    #PRESTAMOS => BACKEND
    Route::prefix('prestamo')->group(function () {
        Route::get('/', [PrestamosController::class, 'index'])->name('api.prestamo.index');
        Route::get('/cliente', [PrestamosController::class, 'indexcliente'])->name('api.prestamo.indexcliente');
        Route::post('/', [PrestamosController::class, 'store'])->name('prestamo.store');
        Route::get('{prestamos}', [PrestamosController::class, 'show'])->name('prestamo.show');
        Route::put('{prestamo}', [PrestamosController::class, 'update'])->name('prestamo.update');
        Route::delete('/{prestamo}/destroy', [PrestamosController::class, 'destroy'])->name('prestamo.destroy');
        Route::get('/{id}/Cuotas', [PrestamosController::class, 'ConsultarPrestamo'])->name('prestamos.ConsultarPrestamo');
        Route::get('/{id}/Talonario/cutas', [PrestamosController::class, 'consultaTalonario'])->name('prestamos.consultaTalonario');
    });

    #PAGO => BACKEND
    Route::prefix('pago')->group(function () {
        Route::get('/cuota/{cuotaId}', [PagosController::class, 'pagosPorCuota']);
    });

    #REPORTE => PAGO
    Route::prefix('reporte')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('reporte.index');
        Route::get('/total/{anio}', [ReporteController::class, 'clientesPorAnio'])->name('cliente.clientesPorAnio');
        Route::get('/capital/{anio}', [ReporteController::class, 'CantidadEmprestada'])->name('reporte.capitalPorAnio');
    });

    #CUOTA => BACKNED
    Route::prefix('cuota')->group(function (): void {
        Route::get('/{prestamo_id}', [CuotasController::class, 'list'])->name('cuota.list');
        Route::post('/', [CuotasController::class, 'pagarCuota'])->name('cuota.pagarCuota');
    });

    #USUARIOS -> BACKEND
    Route::prefix('usuarios')->group(function(){
        Route::get('/', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/',[UsuariosController::class, 'store'])->name('usuarios.store');
        Route::get('/{user}',[UsuariosController::class, 'show'])->name('usuarios.show');
        Route::put('/{user}',[UsuariosController::class, 'update'])->name('usuarios.update');
        Route::delete('/{user}',[UsuariosController::class, 'destroy'])->name('usuarios.destroy');
    });
    #ROLES => BACKEND
    Route::prefix('rol')->group(function () {
        Route::get('/', [RolesController::class, 'index'])->name('roles.index');
        Route::get('/Permisos', [RolesController::class, 'indexPermisos'])->name('roles.indexPermisos');
        Route::post('/', [RolesController::class, 'store'])->name('roles.store');
        Route::get('/{id}', [RolesController::class, 'show'])->name('roles.show');
        Route::put('/{id}', [RolesController::class, 'update'])->name('roles.update');
        Route::delete('/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');
    });
}); 

// Archivos de configuración adicionales
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';