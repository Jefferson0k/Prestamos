<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CuotasModelo;
use App\Models\PagosModelo;
use App\Models\PrestamosModelo;
use App\Services\PagoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PagosController extends Controller{
    protected $pagoService;
    public function __construct(PagoService $pagoService) {
        $this->pagoService = $pagoService;
    }
    public function index() {
        $pagos = PagosModelo::with(['prestamo.cliente', 'cuota'])->latest()->paginate(10);
        return response()->json($pagos);
    }
    public function create(){
        $prestamos = PrestamosModelo::with('cliente')->get();
        return response()->json($prestamos);
    }    
    public function getCuotas(Request $request, PrestamosModelo $prestamo){
        $cuotas = $prestamo->cuotas()
            ->where('estado', '!=', 'Pagado')
            ->orderBy('numero_cuota')
            ->get();
            
        return response()->json($cuotas);
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'fecha_pago' => 'required|date',
        ]);
        $cuota = CuotasModelo::findOrFail($validatedData['cuota_id']);
        $pago = $this->pagoService->registrarPago($cuota, $validatedData['fecha_pago']);
        return response()->json([
            'message' => 'Pago registrado correctamente.',
            'pago' => $pago,
        ]);
    }
    public function show(PagosModelo $pago){
        return response()->json($pago);
    }
}
