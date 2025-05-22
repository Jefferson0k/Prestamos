<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TipoCliente\StoreTipoClienteRequests;
use App\Http\Requests\TipoCliente\UpdateTipoClienteRequests;
use App\Http\Resources\TipoCliente\TipoClienteResource;
use App\Models\TipoCliente;
use App\Filters\Common\GenericEstadoFilter;
use App\Filters\Common\GenericNombreFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pipeline\Pipeline;

class TipoClienteController extends Controller{
    public function index(Request $request){
        Gate::authorize('viewAny', TipoCliente::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $state = $request->input('estado');
        $query = app(Pipeline::class)
            ->send(TipoCliente::query())
            ->through([
                new GenericNombreFilter($search, 'nombre'),
                new GenericEstadoFilter($state, 'estado'),
            ])
            ->thenReturn();
        return TipoClienteResource::collection($query->paginate($perPage));
    }
    public function store(StoreTipoClienteRequests $request){
        Gate::authorize('create', TipoCliente::class);
        $validated = $request->validated();
        $exists = TipoCliente::whereRaw('LOWER(nombre) = ?', [strtolower($validated['nombre'])])->exists();
        if ($exists) {
            return response()->json([
                'errors' => ['nombre' => ['Este nombre ya está registrado.']]
            ], 422);
        }
        $tipocliente = TipoCliente::create($validated);
        return response()->json([
            'state' => true,
            'message' => 'Tipo de cliente registrado correctamente.',
            'tipocliente' => new TipoClienteResource($tipocliente),
        ]);
    }
    public function show(TipoCliente $tipocliente){
        Gate::authorize('view', $tipocliente);
        return response()->json([
            'state' => true,
            'message' => 'Tipo cliente encontrado.',
            'tipocliente' => new TipoClienteResource($tipocliente),
        ]);
    }
    public function update(UpdateTipoClienteRequests $request, TipoCliente $tipocliente){
        Gate::authorize('update', $tipocliente);
        $validated = $request->validated();
        $exists = TipoCliente::whereRaw('LOWER(nombre) = ?', [strtolower($validated['nombre'])])
            ->where('id', '!=', $tipocliente->id)
            ->exists();
        if ($exists) {
            return response()->json([
                'errors' => ['nombre' => ['Este nombre ya está registrado.']]
            ], 422);
        }
        $tipocliente->update($validated);
        return response()->json([
            'state' => true,
            'message' => 'Tipo cliente actualizado correctamente.',
            'tipocliente' => new TipoClienteResource($tipocliente->refresh()),
        ]);
    }
    public function destroy(TipoCliente $tipocliente){
        Gate::authorize('delete', $tipocliente);
        $tipocliente->delete();
        return response()->json([
            'state' => true,
            'message' => 'Tipo de cliente eliminado correctamente.',
        ]);
    }
}
