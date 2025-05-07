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
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller {
    public function index(Request $request){
        Gate::authorize('viewAny', Cliente::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $estadoCliente = $request->input('estado_cliente');
        
        $searchTerms = [];
        if (!empty($search)) {
            $normalizedSearch = strtolower(trim(preg_replace('/\s+/', ' ', $search)));
            $searchTerms = explode(' ', $normalizedSearch);
        }
        $query = Cliente::query();        
        if (!is_null($estadoCliente)) {
            $query->whereHas('prestamos', function($q) use ($estadoCliente) {
                $q->where('estado_cliente', $estadoCliente)
                ->orderBy('fecha_inicio', 'desc');
            });
        }
        
        $query->with(['prestamos' => function($query) use ($estadoCliente) {
            $subQuery = $query->latest('fecha_inicio')
                ->take(1)
                ->with(['pagos' => function($query) {
                    $query->select('id', 'prestamo_id', 'monto_capital', 'monto_interes', 'monto_total');
                }]);
            
            if (!is_null($estadoCliente)) {
                $subQuery->where('estado_cliente', $estadoCliente);
            }
        }]);
        
        if (!empty($searchTerms)) {
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (strlen($term) < 2) continue;
                    $q->where(function($query) use ($term) {
                        $query->whereRaw("LOWER(dni) LIKE ?", ["%{$term}%"])
                            ->orWhereRaw("LOWER(nombre) LIKE ?", ["%{$term}%"])
                            ->orWhereRaw("LOWER(apellidos) LIKE ?", ["%{$term}%"])
                            ->orWhereRaw("LOWER(telefono) LIKE ?", ["%{$term}%"])
                            ->orWhereRaw("LOWER(direccion) LIKE ?", ["%{$term}%"])
                            ->orWhereRaw("LOWER(correo) LIKE ?", ["%{$term}%"])
                            ->orWhereRaw("LOWER(centro_trabajo) LIKE ?", ["%{$term}%"]);
                    });
                }
            });
        }
        
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
    public function update(UpdateClienteRequest $request, Cliente $cliente){
        Gate::authorize('update', $cliente);

        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->handleFotoUpload($request, $cliente);
        }

        $cliente->update($data);

        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'cliente' => $cliente
        ]);
    }
    public function destroy($id){
        $cliente = Cliente::findOrFail($id);
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
