<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pago\StorePagoRequest;
use App\Http\Requests\Pago\UpdatePagoRequest;
use App\Http\Resources\PagoResource;
use App\Models\PagosModelo;
use Illuminate\Http\Response;

class PagosController extends Controller{
    public function index(){
        $pagos = PagosModelo::with('prestamo')->get();
        return PagoResource::collection($pagos);
    }
    public function store(StorePagoRequest $request){
        $pago = PagosModelo::create($request->validated());

        return response()->json([
            'message' => 'Pago registrado exitosamente',
            'data'    => new PagoResource($pago),
        ], Response::HTTP_CREATED);
    }
    public function show($id){
        $pago = PagosModelo::with('prestamo')->find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], Response::HTTP_NOT_FOUND);
        }

        return new PagoResource($pago);
    }
    public function update(UpdatePagoRequest $request, $id){
        $pago = PagosModelo::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $pago->update($request->validated());

        return response()->json([
            'message' => 'Pago actualizado correctamente',
            'data'    => new PagoResource($pago),
        ]);
    }
    public function destroy($id){
        $pago = PagosModelo::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $pago->delete();

        return response()->json(['message' => 'Pago eliminado correctamente']);
    }
}
