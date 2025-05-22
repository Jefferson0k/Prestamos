<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cuota\CuotaResource;
use App\Http\Resources\Cuota\CuotaInteresResource;
use App\Http\Resources\Cuota\CuotaInteresResourceEdicion;
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
        
        $sumQuery = Cuotas::where('prestamo_id', $prestamo_id);
        
        $cuotas = $query->paginate($perPage);
        
        $sumaInteres = $sumQuery->sum('monto_interes_pagar');
        $sumaCapital = $sumQuery->sum('monto_capital_pagar');
        $sumaTotal = $sumaInteres + $sumaCapital;
        
        return CuotaResource::collection($cuotas)->additional([
            'sumas' => [
                'monto_interes_pagar' => $sumaInteres,
                'monto_capital_pagar' => $sumaCapital,
                'monto_total_pagar' => $sumaTotal,
            ]
        ]);
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
            'monto_capital_pagar' => 'required|numeric|min:0',
            'fecha_pago' => 'nullable|date',
            'dias' => 'nullable|integer|min:0'
        ]);
        $this->pagoService->registrarPago(
            $validated['cuota_id'], 
            $validated['monto_capital_pagar'],
            $validated['fecha_pago'] ?? null,
            $validated['dias'] ?? null
        );
        
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
    public function showInteres($id){
        Gate::authorize('viewAny', Cuotas::class);
        $cuota = Cuotas::findOrFail($id);
        return new CuotaInteresResource($cuota);
    }
    public function showIntereEdicion($id){
        Gate::authorize('viewAny', Cuotas::class);
        $cuota = Cuotas::findOrFail($id);
        return new CuotaInteresResourceEdicion($cuota);
    }    
    public function updateInteresz(Request $request, $id){
        Gate::authorize('viewAny', Cuotas::class);
        $request->validate([
            'monto_interes_pagar' => 'required|numeric|min:0',
        ]);
        $cuota = Cuotas::findOrFail($id);
        $cuota->monto_interes_pagar = $request->monto_interes_pagar;
        $cuota->monto_capital_mas_interes_a_pagar = $request->monto_interes_pagar;
        $cuota->state = false;
        $cuota->save();
        return response()->json([
            'message' => 'Interés actualizado correctamente.',
            'cuota' => $cuota
        ]);
    }
    public function updateInteres(Request $request, $id){
        Gate::authorize('viewAny', Cuotas::class);
        $request->validate([
            'monto_interes_pagar' => 'required|numeric|min:0',
            'monto_capital_mas_interes_a_pagar' => 'required|numeric|min:0',
        ]);
        $cuota = Cuotas::findOrFail($id);
        $cuota->monto_interes_pagar = $request->monto_interes_pagar;
        $cuota->monto_capital_mas_interes_a_pagar = $request->monto_capital_mas_interes_a_pagar;
        $cuota->state = false;
        $cuota->save();
        return response()->json([
            'message' => 'Interés actualizado correctamente.',
            'cuota' => $cuota
        ]);
    }

}
