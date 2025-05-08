<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CuotaResource;
use App\Models\Cuotas;
use Illuminate\Http\Request;
use App\Services\PagoService;

class CuotasController extends Controller{
    public function list($prestamo_id, Request $request) {
        $montoCapitalPagar = $request->query('monto_capital_pagar', 0);

        $cuotas = Cuotas::where('prestamo_id', $prestamo_id)
                        ->orderBy('numero_cuota', 'asc')
                        ->get();

        return CuotaResource::collection($cuotas->map(function ($cuota) use ($montoCapitalPagar) {
            $cuota->Monto_Capital_Pagar = $montoCapitalPagar;
            return $cuota;
        }));
    }
    public function pagarCuota(Request $request){
        $validated = $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto_capital_pagar' => 'required|numeric|min:0'
        ]);

        $service = new PagoService();
        $service->registrarPago($validated['cuota_id'], $validated['monto_capital_pagar']);

        return response()->json(['message' => 'Pago registrado correctamente']);
    }
}
