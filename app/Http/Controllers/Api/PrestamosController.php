<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prestamo\StorePrestamoRequest;
use App\Http\Requests\Prestamo\UpdatePrestamoRequest;
use App\Http\Resources\PrestamoResource;
use App\Models\PrestamosModelo;
use Illuminate\Http\Response;

class PrestamosController extends Controller{
    public function index(){
        $prestamos = PrestamosModelo::with('cliente', 'pagos')->get();
        return PrestamoResource::collection($prestamos);
    }
    public function store(StorePrestamoRequest $request){
        $prestamo = PrestamosModelo::create($request->validated());
        return response()->json([
            'message' => 'Préstamo creado exitosamente',
            'data'    => new PrestamoResource($prestamo),
        ], Response::HTTP_CREATED);
    }
    public function show($id){
        $prestamo = PrestamosModelo::with('cliente', 'pagos')->find($id);
        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return new PrestamoResource($prestamo);
    }
    public function update(UpdatePrestamoRequest $request, $id){
        $prestamo = PrestamosModelo::find($id);
        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $prestamo->update($request->validated());
        return response()->json([
            'message' => 'Préstamo actualizado correctamente',
            'data'    => new PrestamoResource($prestamo),
        ]);
    }
    public function destroy($id){
        $prestamo = PrestamosModelo::find($id);
        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $prestamo->delete();
        return response()->json(['message' => 'Préstamo eliminado correctamente']);
    }
}
