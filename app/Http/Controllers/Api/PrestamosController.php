<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prestamo\StorePrestamoRequest;
use App\Http\Requests\Prestamo\UpdatePrestamoRequest;
use App\Http\Resources\ClienteListResource;
use App\Http\Resources\PrestamoResource;
use App\Models\ClienteModelo;
use App\Models\PrestamosModelo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PrestamosController extends Controller{
    public function index(){
        $prestamos = PrestamosModelo::with('cliente', 'pagos')->get();
        return PrestamoResource::collection($prestamos);
    }
    public function indexCliente(Request $request){
        $query = ClienteModelo::query();
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CAST(id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("nombre ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("apellidos ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("dni ILIKE ?", ["%{$search}%"]);
            });
        }
        $clientes = $query->orderBy('nombre', 'asc')->paginate(10);
        return response()->json([
            'data' => $clientes->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'label' => "{$cliente->id} - {$cliente->nombre} {$cliente->apellidos} ({$cliente->dni})",
                    'value' => $cliente->id,
                ];
            }),
            'pagination' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'next_page_url' => $clientes->nextPageUrl(),
            ],
        ]);
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
