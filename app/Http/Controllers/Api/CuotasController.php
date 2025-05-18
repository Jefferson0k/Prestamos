<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cuota\CuotaResource;
use App\Models\Cliente;
use App\Models\Cuotas;
use App\Models\Pagos;
use App\Services\PagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CuotasController extends Controller{
    protected $pagoService;
    public function __construct(PagoService $pagoService){
        $this->pagoService = $pagoService;
    }
    public function list(Request $request, $prestamo_id){
        Gate::authorize('viewAny', Cuotas::class);
        $perPage = $request->input('per_page', 15);
        $query = Cuotas::where('prestamo_id', $prestamo_id)
                    ->orderBy('numero_cuota', 'asc');
        if ($request->filled('estado')) {
            $query->where('estado', 'like', '%' . $request->estado . '%');
        }
        $cuotas = $query->paginate($perPage);
        return CuotaResource::collection($cuotas);
    }

    public function cuotasPorPrestamo($prestamoId){
        Gate::authorize('viewAny', Cuotas::class);
        $cuotas = Cuotas::where('prestamo_id', $prestamoId)
                        ->orderBy('numero_cuota', 'asc')
                        ->get();
        return CuotaResource::collection($cuotas);
    }
    public function actualizar(Request $request, $id){
        $cuota = Cuotas::findOrFail($id);
        $cuota->monto_capital_pagar = $request->input('monto_capital_pagar');
        $cuota->save();
        return response()->json(['message' => 'Pago actualizado correctamente']);
    }
    public function pagarCuota(Request $request){
        Gate::authorize('create', Pagos::class);
        $validated = $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto_capital_pagar' => 'required|numeric|min:0'
        ]);
        $this->pagoService->registrarPago($validated['cuota_id'], $validated['monto_capital_pagar']);
        return response()->json(['message' => 'Pago registrado correctamente']);
    }    
    public function Pendientes(){
        $clientes = Cliente::whereHas('cuotas', function($query) {
            $query->pendientes();
        })
        ->with(['cuotas' => function($query) {
            $query->pendientes();
        }])
        ->paginate(15);
        return $clientes;
    }
}
