<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\StoreClienteRequest;
use App\Http\Requests\Cliente\UpdateClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClienteController extends Controller {
    public function index(Request $request){
        Gate::authorize('viewAny', Cliente::class);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $estadoCliente = $request->input('estado_cliente');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        $query = Cliente::query()
            ->with(['prestamos' => function($query) {
                $query->latest()->with('pagos');
            }]);
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('dni', 'ILIKE', "%{$search}%")
                ->orWhere('nombre', 'ILIKE', "%{$search}%")
                ->orWhere('apellidos', 'ILIKE', "%{$search}%")
                ->orWhere('telefono', 'ILIKE', "%{$search}%")
                ->orWhere('direccion', 'ILIKE', "%{$search}%")
                ->orWhere('correo', 'ILIKE', "%{$search}%")
                ->orWhere('centro_trabajo', 'ILIKE', "%{$search}%");
            });
        }
        
        if (!is_null($estadoCliente)) {
            $query->whereHas('prestamos', function($q) use ($estadoCliente) {
                $q->where('estado_cliente', $estadoCliente);
            });
        }
        
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $query->whereHas('prestamos', function($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
            });
        }
        
        $today = date('Y-m-d');
        $query->whereHas('prestamos', function($q) use ($today) {
            $q->where('created_at', 'ILIKE', "{$today}%")
            ->orWhereHas('pagos', function($subQ) use ($today) {
                $subQ->where('created_at', 'ILIKE', "{$today}%");
            });
        });
        
        $clientes = $query->paginate($perPage);
        
        return ClienteResource::collection($clientes);
    }
    public function store(StoreClienteRequest $request) {
        Gate::authorize('create', Cliente::class);
        $data = $request->validated();
        $data['foto'] = $this->handleFotoUpload($request);
        $cliente = Cliente::create($data);
        return response()->json([
            'message' => 'Cliente registrado exitosamente',
            'cliente' => $cliente
        ], 201);
    }
    public function show(Cliente $cliente) {
        Gate::authorize('view', $cliente);
        return response()->json($cliente);
    }
    public function update(UpdateClienteRequest $request, Cliente $cliente) {
        Gate::authorize('update', $cliente);
        $data = $request->validated();
        $data['foto'] = $this->handleFotoUpload($request, $cliente);
        $cliente->update($data);
        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'cliente' => $cliente
        ]);
    }
    public function destroy(Cliente $cliente) {
        Gate::authorize('delete', $cliente);
        if ($cliente->foto) {
            Storage::delete('public/customers/' . $cliente->foto);
        }
        $cliente->delete();
        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
    private function handleFotoUpload(Request $request, Cliente $cliente = null) {
        if (!$request->hasFile('foto')) return $cliente?->foto;
        if ($cliente && $cliente->foto) {
            if (file_exists(public_path('customers/' . $cliente->foto))) {
                unlink(public_path('customers/' . $cliente->foto));
            }
        }
        $file = $request->file('foto');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('customers'), $fileName);
        return $fileName;
    }
}
