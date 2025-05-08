<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuotas;
use App\Models\Pagos;
use App\Models\Prestamos;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PagosController extends Controller{
    public function index() {
        $pagos = Pagos::with(['prestamo.cliente', 'cuota'])->latest()->paginate(10);
        return response()->json($pagos);
    }
    public function create(){
        $prestamos = Prestamos::with('cliente')->get();
        return response()->json($prestamos);
    }
}
